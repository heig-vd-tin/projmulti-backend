<?php

// Messages de validation en français (sous-ensemble des règles utilisées par l'application).
return [
    'array' => 'Le champ :attribute doit être une liste.',
    'distinct' => 'Le champ :attribute contient un doublon.',
    'exists' => "La valeur choisie pour :attribute n'existe pas ou n'est pas disponible.",
    'gt' => ['numeric' => 'Le champ :attribute doit être supérieur à :value.'],
    'in' => 'La valeur du champ :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min élément(s).',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être du texte.',
    'attributes' => [
        'title' => 'titre',
        'short_description' => 'résumé court',
        'description' => 'description',
        'domains' => 'domaines',
        'projects' => 'projets',
        'projects.*.id' => 'projet',
        'year' => 'année',
    ],
];
