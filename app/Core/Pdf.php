<?php

namespace App\Core;

/**
 * Génération des documents en PDF (facture, reçu, contrat).
 * Utilise la bibliothèque FPDF embarquée (app/Lib/FPDF/fpdf.php).
 */
require_once ROOT . '/app/Lib/FPDF/fpdf.php';

class Pdf extends \FPDF
{
    private const BLUE    = [37, 99, 235];
    private const BLUE_D  = [29, 78, 216];
    private const GREEN   = [16, 185, 129];
    private const INK     = [15, 23, 42];
    private const GRAY    = [100, 116, 139];
    private const LIGHT   = [226, 232, 240];
    private const BG      = [244, 246, 248];

    private const CONTENT_W = 180;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins(15, 20, 15);
        $this->SetAutoPageBreak(true, 22);
    }

    /* =========================================================
     *  ENCODAGE & UTILITAIRES
     * ========================================================= */

    private function t(?string $text): string
    {
        $text = (string) $text;
        $text = str_replace('→', ' au ', $text);
        $text = iconv('UTF-8', 'CP1252//TRANSLIT', $text);
        return $text === false ? '' : $text;
    }

    private function useFont(string $style = '', float $size = 9.5): void
    {
        $this->SetFont('Helvetica', $style, $size);
    }

    /** Nombre de lignes rendues par MultiCell pour un texte donné. */
    private function countLines(float $width, string $text, string $style = ''): int
    {
        $this->useFont($style, 8.5);
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

    private function checkPageBreak(float $h): void
    {
        if ($this->GetY() + $h > 272) {
            $this->AddPage();
            $this->SetY(20);
        }
    }

    /* =========================================================
     *  EN-TÊTE / PIED DE PAGE
     * ========================================================= */

    public function Header(): void
    {
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(...self::GRAY);
        $this->SetY(10);
        $this->Cell(0, 4, $this->t(APP_NAME . ' — 12 rue de la République, 75011 Paris · SIRET 123 456 789 00012'), 0, 1, 'C');
        $this->SetDrawColor(...self::LIGHT);
        $this->Line(15, 16, 195, 16);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(...self::GRAY);
        $this->Cell(0, 4, $this->t('Document généré par ' . APP_NAME . ' — contact@rentova.fr'), 0, 1, 'C');
        $this->Cell(0, 4, $this->t('Page ' . $this->PageNo() . ' / {nb}'), 0, 0, 'C');
    }

    /**
     * En-tête du document : logo + nom (gauche) et titre / n° / date (droite).
     */
    private function docHeader(string $title, string $number, string $date): void
    {
        $this->AddPage();

        // Logo + identité
        $this->SetFillColor(...self::BLUE);
        $this->Rect(15, 20, 14, 14, 'F');
        $this->SetFont('Helvetica', 'B', 13);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(15, 24);
        $this->Cell(14, 7, 'EL', 0, 0, 'C');

        $this->SetTextColor(...self::INK);
        $this->useFont('B', 16);
        $this->SetXY(34, 22);
        $this->Cell(0, 7, $this->t(APP_NAME), 0, 1);
        $this->SetX(34);
        $this->useFont('', 8.5);
        $this->SetTextColor(...self::GRAY);
        $this->Cell(0, 5, $this->t(APP_TAGLINE), 0, 1);

        // Titre du document (droite)
        $this->useFont('B', 21);
        $this->SetTextColor(...self::BLUE_D);
        $this->SetXY(15, 21);
        $this->Cell(180, 9, $this->t($title), 0, 0, 'R');
        $this->SetXY(15, 32);
        $this->useFont('B', 10);
        $this->SetTextColor(...self::INK);
        $this->Cell(180, 5, $this->t('N° ' . $number), 0, 0, 'R');
        $this->SetXY(15, 38);
        $this->useFont('', 9);
        $this->SetTextColor(...self::GRAY);
        $this->Cell(180, 5, $this->t('Date : ' . $date), 0, 1, 'R');

        // Séparateur + bandeau
        $this->SetDrawColor(...self::BLUE);
        $this->SetLineWidth(0.7);
        $this->Line(15, 46, 195, 46);
        $this->SetLineWidth(0.2);
        $this->SetY(54);
    }

    /* =========================================================
     *  BLOCS MISE EN PAGE
     * ========================================================= */

    /** Titre de section (bleu, souligné). */
    private function sectionTitle(string $text): void
    {
        $this->SetTextColor(...self::BLUE_D);
        $this->useFont('B', 11.5);
        $this->Ln(2);
        $this->Cell(0, 7, $this->t($text), 0, 1);
        $this->SetDrawColor(...self::BLUE);
        $this->SetLineWidth(0.4);
        $this->Line(15, $this->GetY() + 0.5, 60, $this->GetY() + 0.5);
        $this->SetLineWidth(0.2);
        $this->Ln(2.5);
        $this->SetTextColor(...self::INK);
    }

    /** Paragraphe pleine largeur. */
    private function paragraph(string $text, float $size = 9.5, string $style = ''): void
    {
        $this->useFont($style, $size);
        $this->SetTextColor(...self::INK);
        $this->MultiCell(0, 5.4, $this->t($text), 0, 'L');
        $this->Ln(2);
    }

    /**
     * Bloc adresse (fond gris clair, auto-hauteur).
     * Retourne la hauteur utilisée.
     */
    private function addressBlock(string $title, array $lines, float $w = 88): float
    {
        $x = $this->GetX();
        $y = $this->GetY();

        $titleLines = $this->countLines($w - 8, $title, 'B');
        $bodyLines = 0;
        foreach ($lines as $line) {
            $bodyLines += $this->countLines($w - 8, (string) $line, '');
        }
        $height = 5 + $titleLines * 4.2 + $bodyLines * 5.5 + 6;

        $this->SetFillColor(...self::BG);
        $this->SetDrawColor(...self::LIGHT);
        $this->Rect($x, $y, $w, $height, 'DF');

        $this->SetXY($x + 4, $y + 4);
        $this->useFont('B', 8);
        $this->SetTextColor(...self::GRAY);
        $this->MultiCell($w - 8, 4.2, $this->t(strtoupper($title)), 0, 'L');

        $this->SetTextColor(...self::INK);
        $this->useFont('', 9.5);
        $yy = $this->GetY() + 2;
        foreach ($lines as $line) {
            $this->SetXY($x + 4, $yy);
            $this->MultiCell($w - 8, 5.5, $this->t((string) $line), 0, 'L');
            $yy = $this->GetY();
        }
        $this->SetXY($x, $y + $height);
        return $height;
    }

    /** Plusieurs blocs adresse côte à côte. */
    private function addressBlocks(array $blocks): void
    {
        $w = 88;
        $gap = 4;
        $x0 = 15;
        $y0 = $this->GetY();
        $heights = [];
        foreach ($blocks as $i => [$title, $lines]) {
            $this->SetXY($x0 + $i * ($w + $gap), $y0);
            $heights[] = $this->addressBlock($title, $lines, $w);
        }
        $this->SetXY(15, $y0 + max($heights) + 5);
    }

    /** Grille d'infos : paires libellé / valeur sur 2 colonnes (auto-hauteur). */
    private function infoBox(array $items): void
    {
        $w = 88;
        $gap = 4;
        $x0 = 15;
        $y = $this->GetY();
        $bottom = $y;
        $this->SetFillColor(...self::BG);
        $this->SetDrawColor(...self::LIGHT);
        foreach (array_chunk($items, 2) as $rowItems) {
            $hMax = 12;
            foreach ($rowItems as [, $value]) {
                $n = $this->countLines($w - 8, (string) $value, 'B');
                $hMax = max($hMax, 5 + $n * 5.5 + 4);
            }
            $bottom = $y + $hMax;
            foreach ($rowItems as $c => [$label, $value]) {
                $x = $x0 + $c * ($w + $gap);
                $this->Rect($x, $y, $w, $hMax, 'DF');
                $this->SetXY($x + 4, $y + 2);
                $this->useFont('', 7);
                $this->SetTextColor(...self::GRAY);
                $this->Cell($w - 8, 3, $this->t($label), 0, 1);
                $this->SetXY($x + 4, $y + 6);
                $this->useFont('B', 9.5);
                $this->SetTextColor(...self::INK);
                $this->MultiCell($w - 8, 5.5, $this->t((string) $value), 0, 'L');
            }
            $y = $bottom + 2;
        }
        $this->SetXY($x0, $bottom + 3);
    }

    /* =========================================================
     *  TABLEAUX (retour à la ligne automatique)
     * ========================================================= */

    private function tableHeader(array $headers, array $widths, array $aligns): void
    {
        $this->SetFillColor(...self::BLUE);
        $this->SetTextColor(255, 255, 255);
        $this->useFont('B', 8.5);
        $x = 15;
        $y = $this->GetY();
        foreach ($headers as $i => $label) {
            $this->Rect($x, $y, $widths[$i], 8, 'F');
            $this->SetXY($x + 2, $y + 2);
            $this->Cell($widths[$i] - 4, 4.5, $this->t($label), 0, 0, $aligns[$i]);
            $x += $widths[$i];
        }
        $this->SetXY(15, $y + 8);
        $this->SetTextColor(...self::INK);
    }

    private function tableRow(array $cells, array $widths, array $aligns, bool $bold = false): void
    {
        $style = $bold ? 'B' : '';
        $this->useFont($style, 8.5);

        $nb = 1;
        foreach ($cells as $i => $cell) {
            $nb = max($nb, $this->countLines($widths[$i] - 4, (string) $cell, $style));
        }
        $h = 5.4 * $nb + 1.6;
        $this->checkPageBreak($h);
        $y = $this->GetY();

        $this->SetDrawColor(...self::LIGHT);
        $x = 15;
        foreach ($cells as $i => $cell) {
            if ($bold) {
                $this->SetFillColor(...self::BG);
                $this->Rect($x, $y, $widths[$i], $h, 'DF');
            } else {
                $this->Rect($x, $y, $widths[$i], $h, 'D');
            }
            $x += $widths[$i];
        }

        $this->useFont($style, 8.5);
        $this->SetTextColor(...self::INK);
        $x = 15;
        foreach ($cells as $i => $cell) {
            $this->SetXY($x + 2, $y + 1);
            $this->MultiCell($widths[$i] - 4, 5.4, $this->t((string) $cell), 0, $aligns[$i]);
            $x += $widths[$i];
        }
        $this->SetXY(15, $y + $h);
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
        $pdf->docHeader('FACTURE', $loc['reference'], date_fr(substr($loc['cree_le'], 0, 10)));

        // Adresses
        $pdf->addressBlocks([
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

        // Infos
        $pdf->infoBox([
            ['Location', $loc['reference']],
            ['Équipement', $loc['equipement_nom'] . ' (' . $loc['marque'] . ' ' . $loc['modele'] . ')'],
            ['Période', date_fr($loc['date_debut']) . ' au ' . date_fr($loc['date_fin'])],
            ['Quantité', '× ' . (int) $loc['quantite']],
        ]);
        $pdf->Ln(4);

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
        $pdf->Ln(6);

        // Modalités
        $pdf->sectionTitle('Modalités de paiement');
        $pdf->paragraph(
            'Règlement à réception de la présente facture, sous 15 jours. '
            . 'Paiement par virement bancaire, carte bancaire ou chèque à l\'ordre de ' . APP_NAME . '. '
            . 'Une caution de ' . FRAIS_CAUTION . ' € est restituée après contrôle du retour du matériel.'
        );
        $pdf->paragraph(
            'Merci de votre confiance. Toute réclamation doit être adressée sous 8 jours. — '
            . APP_NAME . ', SIRET 123 456 789 00012, TVA FR 12 345678901.'
        );
        $pdf->SetTextColor(...self::GRAY);
        $pdf->useFont('', 8.5);
        $pdf->Cell(0, 5, $pdf->t('Générée le ' . date_fr(date('Y-m-d'))), 0, 1);

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
        $pdf->docHeader('REÇU DE PAIEMENT', $loc['reference'] . '-R', date_fr(date('Y-m-d')));

        // Montant encaissé + tampon PAYÉ
        $y0 = $pdf->GetY();
        $pdf->SetFillColor(...self::BLUE);
        $pdf->Rect(15, $y0, 88, 36, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->useFont('', 9);
        $pdf->SetXY(19, $y0 + 6);
        $pdf->Cell(80, 5, $pdf->t('MONTANT ENCAISSÉ (TTC)'), 0, 1);
        $pdf->useFont('B', 19);
        $pdf->SetX(19);
        $pdf->Cell(80, 11, $pdf->t(montant($ttc)), 0, 1);
        $pdf->useFont('', 8.5);
        $pdf->SetX(19);
        $pdf->Cell(80, 5, $pdf->t('Soit ' . number_format($ttc, 2, ',', ' ') . ' € TTC, acquitté.'), 0, 1);

        $pdf->SetFillColor(...self::GREEN);
        $pdf->Rect(107, $y0, 88, 36, 'F');
        $pdf->useFont('B', 26);
        $pdf->SetXY(107, $y0 + 8);
        $pdf->Cell(88, 12, $pdf->t('PAYÉ'), 0, 1, 'C');
        $pdf->useFont('', 9);
        $pdf->SetX(107);
        $pdf->Cell(88, 5, $pdf->t('Règlement intégral'), 0, 1, 'C');
        $pdf->SetTextColor(...self::INK);
        $pdf->SetY($y0 + 42);

        // Payé par
        $pdf->addressBlocks([
            ['Payé par', [
                trim($loc['client_prenom'] . ' ' . $loc['client_nom']),
                $loc['client_adresse'] ?: '—',
                $loc['client_email'],
            ]],
        ]);

        // Infos
        $pdf->infoBox([
            ['Location', $loc['reference']],
            ['Équipement', $loc['equipement_nom']],
            ['Période', date_fr($loc['date_debut']) . ' au ' . date_fr($loc['date_fin'])],
            ['Retour effectif', date_fr($loc['date_retour_effectif'])],
        ]);
        $pdf->Ln(4);

        // Tableau
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
        $pdf->Ln(6);

        // Objet
        $pdf->sectionTitle('Objet du reçu');
        $pdf->paragraph(
            'Ce document atteste du règlement intégral de la location référencée ci-dessus. '
            . 'La caution éventuellement versée n\'est pas incluse dans ce montant et reste soumise aux conditions générales.'
        );
        $pdf->useFont('B', 9.5);
        $pdf->SetTextColor(...self::BLUE_D);
        $pdf->Cell(0, 6, $pdf->t('Merci de votre confiance — à très bientôt chez ' . APP_NAME . ' !'), 0, 1);

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
        $pdf->docHeader('CONTRAT DE LOCATION', $loc['reference'], date_fr(substr($loc['cree_le'], 0, 10)));

        // 1 · Les parties
        $pdf->sectionTitle('1 · Les parties');
        $pdf->addressBlocks([
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
        $pdf->useFont('', 9.5);
        $pdf->SetTextColor(...self::INK);
        foreach ($conditions as $i => $cond) {
            $lines = $pdf->countLines(173, $cond, '');
            $pdf->checkPageBreak($lines * 5.4);
            $pdf->SetX(17);
            $pdf->useFont('B', 9.5);
            $pdf->SetTextColor(...self::BLUE_D);
            $pdf->Cell(7, 5.4, $pdf->t(($i + 1) . '.'));
            $pdf->useFont('', 9.5);
            $pdf->SetTextColor(...self::INK);
            $pdf->MultiCell(0, 5.4, $pdf->t($cond), 0, 'L');
            $pdf->SetX(17);
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
            ['Le Loueur', APP_NAME, 'Fait à Paris, le ' . date_fr(date('Y-m-d')), 'Signature et cachet'],
            ['Le Preneur', trim($loc['client_prenom'] . ' ' . $loc['client_nom']), 'Fait le ' . date_fr(date('Y-m-d')), '« Lu et approuvé » — Signature'],
        ]);

        return $pdf->Output('S');
    }

    /** Zones de signature côte à côte. */
    private function signatureBlocks(array $blocks): void
    {
        $w = 88;
        $h = 46;
        $gap = 4;
        $x0 = 15;
        $y0 = $this->GetY();
        foreach ($blocks as $i => [$title, $name, $dateLine, $note]) {
            $x = $x0 + $i * ($w + $gap);
            $this->SetFillColor(...self::BG);
            $this->SetDrawColor(...self::LIGHT);
            $this->Rect($x, $y0, $w, $h, 'DF');
            $this->SetXY($x + 4, $y0 + 5);
            $this->useFont('B', 8);
            $this->SetTextColor(...self::GRAY);
            $this->Cell($w - 8, 4, $this->t(strtoupper($title)), 0, 1);
            $this->SetXY($x + 4, $y0 + 11);
            $this->useFont('B', 11);
            $this->SetTextColor(...self::INK);
            $this->Cell($w - 8, 6, $this->t($name), 0, 1);
            $this->SetXY($x + 4, $y0 + 19);
            $this->useFont('', 8.5);
            $this->SetTextColor(...self::GRAY);
            $this->Cell($w - 8, 4, $this->t($dateLine), 0, 1);
            $this->SetDrawColor(...self::INK);
            $this->SetLineWidth(0.5);
            $this->Line($x + 10, $y0 + 36, $x + $w - 10, $y0 + 36);
            $this->SetLineWidth(0.2);
            $this->SetXY($x + 4, $y0 + 38);
            $this->useFont('', 8);
            $this->SetTextColor(...self::GRAY);
            $this->Cell($w - 8, 4, $this->t($note), 0, 1);
        }
        $this->SetXY(15, $y0 + $h);
    }
}
