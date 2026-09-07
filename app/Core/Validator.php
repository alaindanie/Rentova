<?php

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Vérifie un ensemble de règles. */
    public function validate(array $rules): self
    {
        foreach ($rules as $field => $label) {
            $chain = $label;
            $value = trim((string) ($this->data[$field] ?? ''));
            if (is_array($chain)) {
                $label = $chain[0] ?? $field;
                $chain = $chain[1] ?? '';
            }
            $this->applyRule($field, $value, (string) $chain, $label);
        }
        return $this;
    }

    // applique toutes les règles sur un champ
    private function applyRule(string $field, string $value, string $chain, string $label): void
    {
        if ($chain === '') {
            return;
        }
        $rules = explode('|', $chain);
        foreach ($rules as $rule) {
            $param = null;
            if (str_contains($rule, ':')) {
                [$rule, $param] = explode(':', $rule, 2);
            }

            switch ($rule) {
                case 'required':
                    if ($value === '') {
                        $this->errors[$field][] = "Le champ « {$label} » est obligatoire.";
                    }
                    break;

                case 'email':
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être un e-mail valide.";
                    }
                    break;

                case 'min':
                    if ($value !== '' && mb_strlen($value) < (int) $param) {
                        $this->errors[$field][] = "Le champ « {$label} » doit contenir au moins {$param} caractères.";
                    }
                    break;

                case 'max':
                    if (mb_strlen($value) > (int) $param) {
                        $this->errors[$field][] = "Le champ « {$label} » ne doit pas dépasser {$param} caractères.";
                    }
                    break;

                case 'numeric':
                    if ($value !== '' && !is_numeric($value)) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être numérique.";
                    }
                    break;

                case 'int':
                    if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être un entier.";
                    }
                    break;

                case 'min_val':
                    if ($value !== '' && (float) $value < (float) $param) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être supérieur ou égal à {$param}.";
                    }
                    break;

                case 'max_val':
                    if ($value !== '' && (float) $value > (float) $param) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être inférieur ou égal à {$param}.";
                    }
                    break;

                case 'date':
                    if ($value !== '' && !preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                        $this->errors[$field][] = "Le champ « {$label} » doit être une date valide.";
                    }
                    break;

                case 'after':
                    if ($value !== '' && $param && $value <= $param) {
                        $this->errors[$field][] = "La date du champ « {$label} » doit être postérieure.";
                    }
                    break;

                case 'after_or_equal':
                    if ($value !== '' && $param && $value < $param) {
                        $this->errors[$field][] = "La date du champ « {$label} » doit être postérieure ou égale.";
                    }
                    break;

                case 'same':
                    if ($value !== trim((string) ($this->data[$param] ?? ''))) {
                        $this->errors[$field][] = "Les champs « {$label} » doivent être identiques.";
                    }
                    break;

                case 'in':
                    $allowed = explode(',', (string) $param);
                    if ($value !== '' && !in_array($value, $allowed, true)) {
                        $this->errors[$field][] = "La valeur du champ « {$label} » n'est pas autorisée.";
                    }
                    break;
            }
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $list) {
            if (!empty($list)) {
                return $list[0];
            }
        }
        return null;
    }
}
