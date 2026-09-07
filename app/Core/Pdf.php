<?php

namespace App\Core;

/**
 * Génération des documents en PDF (facture, reçu, contrat).
 * Reproduit le design des documents HTML affichés sur le site
 * (layout document.php + styles .invoice / .contract).
 * Utilise la bibliothèque FPDF embarquée (app/Lib/FPDF/fpdf.php).
 */
require_once ROOT . '/app/Lib/FPDF/fpdf.php';

class Pdf extends \FPDF
{
    /* Palette du site (style.css :root) */
    private const PRIMARY   = [37, 99, 235];        // #2563eb
    private const PRIMARY_D = [29, 78, 216];        // #1d4ed8
    private const SUCCESS   = [22, 163, 74];        // #16a34a
    private const INK       = [15, 23, 42];         // #0f172a
    private const TEXT      = [51, 65, 85];         // #334155
    private const MUTED     = [100, 116, 139];      // #64748b
    private const LINE      = [226, 232, 240];      // #e2e8f0
    private const BG        = [248, 250, 252];      // #f8fafc

    private const CONTENT_W = 180;
    private const ML = 15;                          // marge gauche

    private string $logoFile = '';

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins(self::ML, 16, self::ML);
        $this->SetAutoPageBreak(true, 20);
        $logo = PUBLIC_DIR . '/assets/img/logo.png';
        $this->logoFile = file_exists($logo) ? $logo : '';
    }

    /* =========================================================
     *  ENCODAGE & UTILITAIRES
     * ========================================================= */

    private function t(?string $text): string
    {
        $text = (string) $text;
        $text = str_replace('→', ' au ', $text);
        $converted = iconv('UTF-8', 'CP1252//TRANSLIT', $text);
        return $converted === false ? '' : $converted;
    }

    private function useFont(string $style = '', float $size = 9.5): void
    {
        $this->SetFont('Helvetica', $style, $size);
    }

    /** Saut de page si la hauteur $h ne tient plus sur la page courante. */
    private function checkPageBreak(float $h): void
    {
        if ($this->GetY() + $h > $this->GetPageHeight() - 20) {
            $this->AddPage();
        }
    }

    /** Nombre de lignes rendues par MultiCell pour un texte donné. */
    private function countLines(float $width, string $text, string $style = '', float $size = 9.5): int
    {
        $this->useFont($style, $size);
        $text = $this->t($text);
        if ($text === '') {
            return 1;
        }
        $nb = 1;
        $w = 0.0;
        foreach (str_split($text) as $ch) {
            $w += $this->GetStringWidth($ch);
            if ($w > $width) {
                $nb++;
                $w = $this->GetStringWidth($ch);
            }
        }
        return $nb;
    }

    /* =========================================================
     *  EN-TÊTE / PIED DE PAGE
     * ========================================================= */

    public function Header(): void
    {
        if ($this->PageNo() <= 1) {
            return;
        }
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(...self::MUTED);
        $this->SetY(8);
        $this->Cell(0, 4, $this->t(APP_NAME . ' — 12 rue de la République, 75011 Paris · SIRET 123 456 789 00012'), 0, 1, 'C');
        $this->SetDrawColor(...self::LINE);
        $this->Line(self::ML, 13, 195, 13);
    }

    public function Footer(): void
    {
        $this->SetY(-16);
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(...self::MUTED);
        $this->Cell(0, 4, $this->t('Document généré par ' . APP_NAME . ' — contact@rentova.fr'), 0, 1, 'C');
        $this->Cell(0, 4, $this->t('Page ' . $this->PageNo() . ' / {nb}'), 0, 0, 'C');
    }

    /**
     * En-tête du document : logo + identité (gauche) et titre / n° / date (droite),
     * puis barre épaisse noire — aligné sur le design .invoice-head du site.
     */
    private function docHeader(string $title, string $number, string $date): void
    {
        $this->AddPage();

        // Logo (le logo du site, carré 1254x1254) + identité
        $size = 13;
        if ($this->logoFile !== '') {
            $this->Image($this->logoFile, self::ML, 16, $size, $size);
            $this->SetXY(self::ML + $size + 5, 16);
        } else {
            $this->SetFillColor(...self::PRIMARY);
            $this->Rect(self::ML, 16, $size, $size, 'F');
            $this->SetFont('Helvetica', 'B', $size - 5);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(self::ML, 18);
            $this->Cell($size, $size - 4, 'R', 0, 0, 'C');
            $this->SetXY(self::ML + $size + 5, 16);
        }

        $this->SetTextColor(...self::INK);
        $this->useFont('B', 15);
        $this->Cell(0, 7, $this->t(APP_NAME), 0, 1);
        $this->SetX(self::ML + $size + 5);
        $this->useFont('', 8);
        $this->SetTextColor(...self::MUTED);
        $this->Cell(0, 5, $this->t(APP_TAGLINE), 0, 1);

        // Titre du document (droite)
        $this->useFont('B', 20);
        $this->SetTextColor(...self::PRIMARY);
        $this->SetXY(self::ML, 17);
        $this->Cell(180, 9, $this->t($title), 0, 0, 'R');
        $this->SetXY(self::ML, 28);
        $this->useFont('B', 10);
        $this->SetTextColor(...self::INK);
        $this->Cell(180, 5, $this->t('N° ' . $number), 0, 0, 'R');
        $this->SetXY(self::ML, 34);
        $this->useFont('', 9);
        $this->SetTextColor(...self::TEXT);
        $this->Cell(180, 5, $this->t($date), 0, 1, 'R');

        // Barre épaisse noire sous l'entête (style border-bottom du site)
        $this->SetDrawColor(...self::INK);
        $this->SetLineWidth(1.1);
        $this->Line(self::ML, 43, 195, 43);
        $this->SetLineWidth(0.2);
        $this->SetY(50);
    }

    /* =========================================================
     *  BLOCS MISE EN PAGE
     * ========================================================= */

    /** Titre de section (violet du site pour le contrat), souligné. */
    private function sectionTitle(string $text, array $color = null): void
    {
        $this->SetTextColor(...($color ?? self::PRIMARY));
        $this->useFont('B', 11.5);
        $this->Ln(2);
        $this->Cell(0, 7, $this->t($text), 0, 1);
        $this->SetDrawColor(...self::LINE);
        $this->SetLineWidth(0.4);
        $this->Line(self::ML, $this->GetY() + 0.5, 195, $this->GetY() + 0.5);
        $this->SetLineWidth(0.2);
        $this->Ln(2.5);
        $this->SetTextColor(...self::TEXT);
    }

    /** Paragraphe pleine largeur. */
    private function paragraph(string $text, float $size = 9.5, string $style = ''): void
    {
        $this->useFont($style, $size);
        $this->SetTextColor(...self::TEXT);
        $this->MultiCell(0, 5.4, $this->t($text), 0, 'L');
        $this->Ln(2);
    }

    /**
     * Bloc de texte sur fond clair .invoice-addr > div.
     * Retourne la hauteur utilisée.
     */
    private function haloBlock(string $title, array $lines, float $w = 88, int $alignTitle = 0): float
    {
        $x = $this->GetX();
        $y = $this->GetY();

        $titleLines = $this->countLines($w - 10, $title, 'B', 7.5);
        $bodyLines = 0;
        foreach ($lines as $line) {
            $bodyLines += $this->countLines($w - 10, (string) $line, '', 9);
        }
        $height = 6 + $titleLines * 4.4 + $bodyLines * 5.2 + 7;

        $this->SetFillColor(...self::BG);
        $this->SetDrawColor(...self::LINE);
        $this->Rect($x, $y, $w, $height, 'F');

        $this->SetXY($x + 5, $y + 4);
        $this->useFont('B', 7.5);
        $this->SetTextColor(...self::MUTED);
        $this->MultiCell($w - 10, 4.4, $this->t(strtoupper($title)), 0, $alignTitle === 2 ? 'R' : 'L');

        $this->SetTextColor(...self::TEXT);
        $this->useFont('', 9);
        $yy = $this->GetY() + 2;
        foreach ($lines as $line) {
            $this->SetXY($x + 5, $yy);
            $this->MultiCell($w - 10, 5.2, $this->t((string) $line), 0, $alignTitle === 2 ? 'R' : 'L');
            $yy = $this->GetY();
        }
        $this->SetXY($x, $y + $height);
        return $height;
    }

    /** Deux blocs côte à côte. */
    private function haloBlocks(array $blocks): void
    {
        $w = 88;
        $gap = 4;
        $x0 = self::ML;
        $y0 = $this->GetY();
        $heights = [];
        foreach ($blocks as $i => [$title, $lines]) {
            $this->SetXY($x0 + $i * ($w + $gap), $y0);
            $heights[] = $this->haloBlock($title, $lines, $w);
        }
        $this->SetXY(self::ML, $y0 + max($heights) + 4);
    }

    /**
     * Grille d'infos (2 colonnes, fond clair) — style .invoice-infos.
     */
    private function infoGrid(array $items): void
    {
        $w = 88;
        $gap = 4;
        $x0 = self::ML;
        $y = $this->GetY();
        $bottom = $y;
        foreach (array_chunk($items, 2) as $rowItems) {
            $hMax = 13;
            foreach ($rowItems as [, $value]) {
                $n = $this->countLines($w - 10, (string) $value, 'B', 9);
                $hMax = max($hMax, 5 + $n * 4.6 + 4);
            }
            $bottom = $y + $hMax;
            foreach ($rowItems as $c => [$label, $value]) {
                $x = $x0 + $c * ($w + $gap);
                if ($value !== null) {
                    $this->SetFillColor(...self::BG);
                    $this->Rect($x, $y, $w, $hMax, 'F');
                }
                $this->SetXY($x + 5, $y + 2);
                $this->useFont('B', 6.5);
                $this->SetTextColor(...self::MUTED);
                $this->Cell($w - 10, 3.4, $this->t(strtoupper($label)), 0, 1);
                $this->SetXY($x + 5, $y + 6);
                $this->useFont('B', 9);
                $this->SetTextColor(...self::INK);
                $this->MultiCell($w - 10, 4.6, $this->t((string) $value), 0, 'L');
            }
            $y = $bottom + 2;
        }
        $this->SetXY($x0, $bottom + 3);
    }

    /* =========================================================
     *  TABLEAUX
     * ========================================================= */

    private function tableHeader(array $headers, array $widths, array $aligns): void
    {
        $this->SetFillColor(...self::BG);
        $this->SetTextColor(...self::MUTED);
        $this->useFont('B', 7);
        $x = self::ML;
        $y = $this->GetY();
        foreach ($headers as $i => $label) {
            $this->Rect($x, $y, $widths[$i], 8, 'F');
            $this->SetXY($x + 3, $y + 2.2);
            $this->Cell($widths[$i] - 4, 4.4, $this->t(strtoupper($label)), 0, 0, $aligns[$i]);
            $x += $widths[$i];
        }
        $this->SetXY(self::ML, $y + 8);
        $this->SetTextColor(...self::TEXT);
    }

    private function tableRow(array $cells, array $widths, array $aligns, bool $grand = false, int $borderType = 1): void
    {
        $style = $grand ? 'B' : '';
        $this->useFont($style, 9);

        $nb = 1;
        foreach ($cells as $i => $cell) {
            $nb = max($nb, $this->countLines($widths[$i] - 6, (string) $cell, $style, 9));
        }
        $h = 5.4 * $nb + 2.6;
        $this->checkPageBreak($h);
        $y = $this->GetY();

        $x = self::ML;
        foreach ($cells as $i => $cell) {
            if ($grand) {
                $this->SetFillColor(...self::BG);
                $this->Rect($x, $y, $widths[$i], $h, 'F');
            } else {
                $this->Rect($x, $y, $widths[$i], $h, 'D');
            }
            $x += $widths[$i];
        }

        if ($grand && $borderType === 1) {
            // bordure supérieure renforcée comme .grand (border-top: 2px solid ink)
            $this->SetDrawColor(...self::INK);
            $this->SetLineWidth(0.8);
            $this->Line(self::ML, $y, self::ML + array_sum($widths), $y);
            $this->SetLineWidth(0.2);
        }

        $this->useFont($style, 9);
        $this->SetTextColor(...self::INK);
        $x = self::ML;
        foreach ($cells as $i => $cell) {
            if ($grand && $i === count($cells) - 1) {
                $this->SetTextColor(...self::PRIMARY);
            }
            $this->SetXY($x + 2, $y + 1.4);
            $this->MultiCell($widths[$i] - 4, 5.4, $this->t((string) $cell), 0, $aligns[$i]);
            if ($grand && $i === count($cells) - 1) {
                $this->SetTextColor(...self::INK);
            }
            $x += $widths[$i];
        }
        $this->SetXY(self::ML, $y + $h);
    }

    /** Bloc gris .invoice-payment : titre + texte. */
    private function paymentBox(string $title, array $texts): void
    {
        $this->Ln(2);
        $y = $this->GetY();
        $n = 1;
        foreach ($texts as $txt) {
            $n += $this->countLines(self::CONTENT_W - 10, $txt, '', 9);
        }
        $h = 6 + 5 + $n * 5.2 + 6;
        $this->SetFillColor(...self::BG);
        $this->Rect(self::ML, $y, self::CONTENT_W, $h, 'F');

        $this->SetXY(self::ML + 6, $y + 4);
        $this->useFont('B', 7);
        $this->SetTextColor(...self::MUTED);
        $this->Cell(0, 4, $this->t(strtoupper($title)), 0, 1);
        $this->SetTextColor(...self::TEXT);
        $this->useFont('', 9);
        foreach ($texts as $txt) {
            $this->SetX(self::ML + 6);
            $this->MultiCell(self::CONTENT_W - 12, 5.2, $this->t($txt), 0, 'L');
        }
        $this->SetY($y + $h + 1);
    }

    /** Pied de facture gris .invoice-foot. */
    private function docFooter(string $left, string $right = ''): void
    {
        $this->Ln(3);
        $y = $this->GetY();
        $this->SetDrawColor(...self::LINE);
        $this->Line(self::ML, $y, 195, $y);
        $this->SetXY(self::ML, $y + 1);
        $this->useFont('', 7.5);
        $this->SetTextColor(...self::MUTED);
        $this->Cell(110, 4, $this->t($left), 0, 0, 'L');
        if ($right !== '') {
            $this->Cell(70, 4, $this->t($right), 0, 0, 'R');
        }
        $this->SetY($y + 6);
    }

    /* =========================================================
     *  FACTURE
     * ========================================================= */

    public static function facture(array $loc): string
    {
        $ht = (float) $loc['montant_base'];
        $frais = (float) $loc['montant_frais'];
        $tva = round(($ht + $frais) * TAUX_TVA / 100, 2);
        $ttc = $ht + $frais + $tva;

        $pdf = new self();
        $pdf->AliasNbPages();
        $pdf->docHeader('FACTURE', $loc['reference'], 'Date : ' . date_fr(substr($loc['cree_le'], 0, 10)));

        // Adresses
        $pdf->haloBlocks([
            ['Émetteur', [
                APP_NAME,
                '12 rue de la République, 75011 Paris',
                'SIRET 123 456 789 00012',
                'contact@rentova.fr · 01 23 45 67 89',
            ]],
            ['Adressée à', [
                trim($loc['client_prenom'] . ' ' . $loc['client_nom']),
                $loc['client_adresse'] ?: '—',
                $loc['client_email'],
                $loc['client_telephone'] ?: '—',
            ]],
        ]);
        $pdf->Ln(2);

        // Infos (grille 2x2)
        $pdf->infoGrid([
            ['Location', $loc['reference']],
            ['Équipement', $loc['equipement_nom'] . ' (' . $loc['marque'] . ' ' . $loc['modele'] . ')'],
            ['Période', date_fr($loc['date_debut']) . ' au ' . date_fr($loc['date_fin'])],
            ['Quantité', '× ' . (int) $loc['quantite']],
        ]);
        $pdf->Ln(3);

        // Tableau
        $w = [80, 18, 18, 28, 36];
        $a = ['L', 'C', 'C', 'R', 'R'];
        $pdf->tableHeader(['Désignation', 'Qté', 'Jours', 'P.U. HT', 'Montant HT'], $w, $a);
        $pdf->tableRow([
            'Location de ' . $loc['equipement_nom'] . ' — ' . $loc['marque'] . ' ' . $loc['modele'],
            (int) $loc['quantite'],
            (int) $loc['duree'],
            montant($loc['prix_jour']),
            montant($ht),
        ], $w, $a);
        if ($frais > 0) {
            $pdf->tableRow([
                'Frais additionnels (retard / dommages / nettoyage)',
                '', '', '', montant($frais),
            ], $w, $a);
        }
        $pdf->tableRow(['', '', '', 'Total HT', montant($ht + $frais)], $w, $a);
        $pdf->tableRow(['', '', '', 'TVA (' . TAUX_TVA . ' %)', montant($tva)], $w, $a);
        $pdf->tableRow(['', '', '', 'TOTAL TTC', montant($ttc)], $w, $a, true);
        $pdf->Ln(5);

        // Habillage .invoice-payment
        $pdf->paymentBox('Modalités de paiement', [
            'Règlement à réception de la présente facture, sous 15 jours. Paiement par virement bancaire, '
            . 'carte bancaire ou chèque à l\'ordre de ' . APP_NAME . '.',
            'Une caution de ' . FRAIS_CAUTION . ' € est restituée après contrôle du retour du matériel.',
        ]);
        $pdf->paymentBox('Réclamations', [
            'Merci de votre confiance. Toute réclamation doit être adressée sous 8 jours. — '
            . APP_NAME . ', SIRET 123 456 789 00012, TVA FR 12 345678901.',
        ]);

        // Pied
        $pdf->docFooter(
            APP_NAME . ' — 12 rue de la République, 75011 Paris — SIRET 123 456 789 00012.',
            'Générée le ' . date_fr(date('Y-m-d'))
        );

        return $pdf->Output('S');
    }

    /* =========================================================
     *  REÇU
     * ========================================================= */

    public static function recu(array $loc): string
    {
        $ht = (float) $loc['montant_base'];
        $frais = (float) $loc['montant_frais'];
        $tva = round(($ht + $frais) * TAUX_TVA / 100, 2);
        $ttc = $ht + $frais + $tva;

        $pdf = new self();
        $pdf->AliasNbPages();
        $pdf->docHeader('REÇU DE PAIEMENT', $loc['reference'] . '-R', 'Date : ' . date_fr(date('Y-m-d')));

        // Timbre PAYÉ + montant (style .invoice-addr 2 colonnes)
        $y0 = $pdf->GetY();

        // Bloc gauche : Payé par
        $pdf->SetXY(self::ML, $y0);
        $leftH = $pdf->haloBlock('Payé par', [
            trim($loc['client_prenom'] . ' ' . $loc['client_nom']),
            $loc['client_adresse'] ?: '—',
            $loc['client_email'],
        ], 88);

        // Bloc droit : Montant encaissé
        $pdf->SetXY(self::ML + 92, $y0);
        $rightH = $pdf->haloBlock('Montant encaissé', [
            montant($ttc),
            'Soit ' . number_format($ttc, 2, ',', ' ') . ' € TTC, acquitté.',
        ], 88, 2);

        // Timbre PAYÉ (bordure verte, titre success) au-dessous du montant
        $stampX = self::ML + 92;
        $stampY = $y0 + $rightH + 3;
        $thisW = 88;
        $stampH = 14;
        $pdf->SetDrawColor(...self::SUCCESS);
        $pdf->SetLineWidth(1.1);
        $pdf->Rect($stampX, $stampY, $thisW, $stampH, 'D');
        $pdf->SetLineWidth(0.2);
        $pdf->SetXY($stampX, $stampY + 3.5);
        $pdf->useFont('B', 14);
        $pdf->SetTextColor(...self::SUCCESS);
        $pdf->Cell($thisW, 8, $pdf->t('PAYÉ'), 0, 0, 'C');

        // position de suite après la colonne la plus haute
        $pdf->SetY(max($leftH, $rightH + 3 + $stampH) + $y0 + 4);
        $pdf->Ln(1);

        // Infos
        $pdf->infoGrid([
            ['Location', $loc['reference']],
            ['Équipement', $loc['equipement_nom']],
            ['Période', date_fr($loc['date_debut']) . ' au ' . date_fr($loc['date_fin'])],
            ['Retour effectif', date_fr($loc['date_retour_effectif'])],
        ]);
        $pdf->Ln(3);

        // Tableau récapitulatif
        $w = [144, 36];
        $a = ['L', 'R'];
        $pdf->tableHeader(['Désignation', 'Montant HT'], $w, $a);
        $pdf->tableRow([
            'Location de ' . $loc['equipement_nom'] . ' (' . (int) $loc['duree'] . ' j × ' . (int) $loc['quantite'] . ')',
            montant($ht),
        ], $w, $a);
        if ($frais > 0) {
            $pdf->tableRow(['Frais additionnels (retard / dommages / nettoyage)', montant($frais)], $w, $a);
        }
        $pdf->tableRow(['TVA (' . TAUX_TVA . ' %)', montant($tva)], $w, $a);
        $pdf->tableRow(['TOTAL TTC ENCAISSÉ', montant($ttc)], $w, $a, true);
        $pdf->Ln(5);

        // Objet
        $pdf->paymentBox('Objet du reçu', [
            'Ce document atteste du règlement intégral de la location référencée ci-dessus. '
            . 'La caution éventuellement versée n\'est pas incluse dans ce montant et reste soumise aux conditions générales.',
        ]);
        $pdf->useFont('B', 9.5);
        $pdf->SetTextColor(...self::PRIMARY);
        $pdf->Cell(0, 6, $pdf->t('Merci de votre confiance — à très bientôt chez ' . APP_NAME . ' !'), 0, 1);

        // Pied
        $pdf->docFooter(
            APP_NAME . ' — 12 rue de la République, 75011 Paris — SIRET 123 456 789 00012.',
            'Reçu généré le ' . date_fr(date('Y-m-d'))
        );

        return $pdf->Output('S');
    }

    /* =========================================================
     *  CONTRAT
     * ========================================================= */

    public static function contrat(array $loc): string
    {
        $ht = (float) $loc['montant_base'];
        $frais = (float) $loc['montant_frais'];
        $tva = round(($ht + $frais) * TAUX_TVA / 100, 2);
        $ttc = $ht + $frais + $tva;

        $pdf = new self();
        $pdf->AliasNbPages();
        $pdf->docHeader('CONTRAT DE LOCATION', $loc['reference'], 'Établi le ' . date_fr(substr($loc['cree_le'], 0, 10)));

        // 1 · Les parties
        $pdf->sectionTitle('1 · Les parties');
        $pdf->haloBlocks([
            ['Le Loueur', [
                APP_NAME . ' — 12 rue de la République, 75011 Paris',
                'SIRET 123 456 789 00012',
                'contact@rentova.fr',
            ]],
            ['Le Preneur', [
                trim($loc['client_prenom'] . ' ' . $loc['client_nom']),
                $loc['client_adresse'] ?: '—',
                $loc['client_email'] . ' — ' . ($loc['client_telephone'] ?: '—'),
            ]],
        ]);
        $pdf->Ln(2);

        // 2 · Objet du contrat
        $pdf->sectionTitle('2 · Objet du contrat');
        $pdf->paragraph(
            'Le présent contrat a pour objet la location du matériel suivant, dont l\'état est réputé '
            . 'conforme à sa fiche catalogue au moment de la remise :'
        );

        $w = [50, 130];
        $a = ['L', 'L'];
        $pdf->tableHeader(['Champ', 'Détail'], $w, $a);
        $pdf->tableRow(['Équipement', $loc['equipement_nom'] . ' — ' . $loc['marque'] . ' ' . $loc['modele']], $w, $a);
        $pdf->tableRow(['Catégorie', $loc['categorie_nom']], $w, $a);
        $pdf->tableRow(['Quantité', (int) $loc['quantite'] . ' exemplaire(s)'], $w, $a);
        $pdf->tableRow([
            'Période',
            'du ' . date_fr($loc['date_debut']) . ' au ' . date_fr($loc['date_fin']) . ' inclus, soit ' . (int) $loc['duree'] . ' jour(s)',
        ], $w, $a);
        $pdf->tableRow(['Tarif', montant($loc['prix_jour']) . ' HT / jour / unité'], $w, $a);
        $pdf->tableRow(['Montant de base', montant($ht) . ' HT'], $w, $a);
        if ($frais > 0) {
            $pdf->tableRow(['Frais additionnels', montant($frais) . ' HT'], $w, $a);
        }
        $pdf->tableRow(['Total', montant($ttc) . ' TTC'], $w, $a, true);
        $pdf->Ln(4);

        // 3 · Conditions d'utilisation
        $pdf->sectionTitle('3 · Conditions d\'utilisation');
        $conditions = [
            'Le matériel reste la propriété exclusive du Loueur et ne peut être cédé, sous-loué ou déplacé hors des lieux convenus sans accord écrit.',
            'Le Preneur s\'engage à utiliser le matériel conformément à sa destination, en respectant les consignes de sécurité et la réglementation en vigueur.',
            'Tout dommage causé au matériel, hors usure normale constatée, sera facturé au Preneur selon le barème de réparation du Loueur.',
            'Le matériel doit être restitué dans les délais impartis. Tout jour de retard entraîne des frais de ' . RETARD_PAR_JOUR . ' € HT par jour et par unité.',
            'Le Preneur déclare être assuré en responsabilité civile couvrant l\'utilisation du matériel loué.',
            'Une caution de ' . FRAIS_CAUTION . ' € est due à la signature et restituée après contrôle du retour, déduction faite des éventuels frais.',
        ];
        foreach ($conditions as $i => $cond) {
            $lines = $pdf->countLines(178, $cond, '', 9.5);
            $pdf->checkPageBreak($lines * 5.4);
            $pdf->SetX(self::ML + 2);
            $pdf->useFont('B', 9.5);
            $pdf->SetTextColor(...self::PRIMARY);
            $pdf->Cell(7, 5.4, $pdf->t(($i + 1) . '.'));
            $pdf->useFont('', 9.5);
            $pdf->SetTextColor(...self::TEXT);
            $pdf->MultiCell(0, 5.4, $pdf->t($cond), 0, 'L');
            $pdf->SetX(self::ML + 2);
        }
        $pdf->Ln(2);

        // 4 · Retour du matériel
        $pdf->sectionTitle('4 · Retour du matériel');
        $pdf->paragraph(
            'Le retour s\'effectue au siège du Loueur aux horaires d\'ouverture, sauf livraison/récupération convenue. '
            . 'L\'état de restitution est constaté contradictoirement par un agent du Loueur ; à défaut, '
            . 'le matériel est réputé restitué en bon état.'
        );

        // 5 · Signatures
        $pdf->sectionTitle('5 · Signatures');
        $pdf->paragraph('Les parties déclarent avoir pris connaissance et accepter les conditions du présent contrat. Fait en deux exemplaires originaux.');

        $pdf->signatureBlocks([
            ['Fait à Paris, le ' . date_fr(date('Y-m-d')), 'Le Loueur', APP_NAME, 'Signature et cachet'],
            ['Fait le ' . date_fr(date('Y-m-d')), 'Le Preneur', trim($loc['client_prenom'] . ' ' . $loc['client_nom']), '« Lu et approuvé » — Signature précédée de la mention'],
        ]);

        // Pied
        $pdf->docFooter('Contrat généré automatiquement depuis la plateforme ' . APP_NAME . '.');

        return $pdf->Output('S');
    }

    /** Zones de signature côte à côte (design .contract-signs). */
    private function signatureBlocks(array $blocks): void
    {
        $w = 88;
        $h = 42;
        $gap = 4;
        $x0 = self::ML;
        $y0 = $this->GetY();
        $this->checkPageBreak($h);
        $y0 = $this->GetY();
        foreach ($blocks as $i => [$dateLine, $title, $name, $note]) {
            $x = $x0 + $i * ($w + $gap);
            $this->SetDrawColor(...self::LINE);
            $this->Rect($x, $y0, $w, $h, 'D');
            $this->SetXY($x + 4, $y0 + 4);
            $this->useFont('', 8);
            $this->SetTextColor(...self::MUTED);
            $this->Cell($w - 8, 4, $this->t($dateLine), 0, 1);
            $this->SetXY($x + 4, $y0 + 12);
            $this->useFont('B', 10);
            $this->SetTextColor(...self::INK);
            $this->Cell($w - 8, 5, $this->t($title), 0, 1);
            $this->SetXY($x + 4, $y0 + 18);
            $this->useFont('B', 10);
            $this->SetTextColor(...self::TEXT);
            $this->MultiCell($w - 8, 5, $this->t($name), 0, 'L');
            $this->SetDrawColor(...self::INK);
            $this->SetLineWidth(0.5);
            $this->Line($x + 8, $y0 + 33, $x + $w - 8, $y0 + 33);
            $this->SetLineWidth(0.2);
            $this->SetXY($x + 4, $y0 + 35);
            $this->useFont('', 7.5);
            $this->SetTextColor(...self::MUTED);
            $this->Cell($w - 8, 4, $this->t($note), 0, 1);
        }
        $this->SetXY(self::ML, $y0 + $h);
    }
}