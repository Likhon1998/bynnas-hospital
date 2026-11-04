<?php

namespace App\Support;

class ClinicalNoteOptions
{
    /**
     * @return list<string>
     */
    public static function for(string $field): array
    {
        return match ($field) {
            'symptoms' => self::symptoms(),
            'examination_notes' => self::examinationNotes(),
            'diagnosis' => self::diagnoses(),
            'treatment_plan' => self::treatmentPlans(),
            'prescriptions' => [],
            'follow_up_instructions' => self::followUps(),
            default => [],
        };
    }

    public static function joiner(string $field): string
    {
        return $field === 'prescriptions' ? "\n" : '; ';
    }

    /**
     * Split a stored clinical string into catalog matches and leftover custom text.
     *
     * @return array{selected: list<string>, other: string}
     */
    public static function hydrate(string $field, ?string $value): array
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return ['selected' => [], 'other' => ''];
        }

        if ($field === 'prescriptions') {
            $lines = preg_split('/\R+/', $raw) ?: [];

            return [
                'selected' => array_values(array_filter(array_map('trim', $lines))),
                'other' => '',
            ];
        }

        $allowed = self::for($field);
        $parts = preg_split('/\s*;\s*/', $raw) ?: [];
        $selected = [];
        $other = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);

            if ($part === '') {
                continue;
            }

            if (in_array($part, $allowed, true)) {
                $selected[] = $part;
            } else {
                $other[] = $part;
            }
        }

        return [
            'selected' => array_values(array_unique($selected)),
            'other' => implode('; ', $other),
        ];
    }

    /**
     * @return list<string>
     */
    public static function symptoms(): array
    {
        return [
            'Fever',
            'Cough',
            'Sore throat',
            'Runny / blocked nose',
            'Headache',
            'Body ache',
            'Weakness / fatigue',
            'Nausea / vomiting',
            'Diarrhoea',
            'Abdominal pain',
            'Chest pain',
            'Shortness of breath',
            'Palpitations',
            'Dizziness',
            'Back pain',
            'Joint pain',
            'Skin rash / itching',
            'Urinary burning / frequency',
            'Ear pain',
            'Eye redness / discharge',
        ];
    }

    /**
     * @return list<string>
     */
    public static function examinationNotes(): array
    {
        return [
            'Looks well, no acute distress',
            'Mild dehydration',
            'Febrile appearance',
            'Throat congested / red',
            'Tonsils enlarged',
            'Chest clear bilaterally',
            'Wheeze present',
            'Creps / crackles present',
            'Abdomen soft, non-tender',
            'Abdomen tender',
            'Normal heart sounds',
            'Tachycardia',
            'Elevated blood pressure',
            'Lymph nodes enlarged',
            'Skin rash noted',
            'ENT examination normal',
            'Neurological exam normal',
        ];
    }

    /**
     * @return list<string>
     */
    public static function diagnoses(): array
    {
        return [
            'Viral fever / viral URI',
            'Acute pharyngitis / tonsillitis',
            'Acute bronchitis',
            'Allergic rhinitis',
            'Bronchial asthma (mild)',
            'Gastroenteritis',
            'Acid peptic disease / gastritis',
            'Migraine / tension headache',
            'Essential hypertension',
            'Type 2 diabetes mellitus (follow-up)',
            'Urinary tract infection',
            'Acute otitis media',
            'Conjunctivitis',
            'Musculoskeletal pain / sprain',
            'Dermatitis / eczema',
            'Anxiety-related somatic symptoms',
            'Post-viral weakness',
        ];
    }

    /**
     * @return list<string>
     */
    public static function treatmentPlans(): array
    {
        return [
            'Rest and adequate fluids',
            'Warm saline gargle',
            'Steam inhalation',
            'Avoid cold drinks / allergens',
            'Light diet; ORS if loose stools',
            'Salt restriction / DASH advice',
            'Continue home medicines as advised',
            'Physiotherapy / local heat',
            'Wound care advice given',
            'Lifestyle and diet counselling',
            'Monitor BP / sugar at home',
            'Refer if symptoms worsen',
        ];
    }

    /**
     * Medicine names only — dose / frequency / duration are chosen per patient.
     *
     * @return list<string>
     */
    public static function medicines(): array
    {
        return [
            'Paracetamol 500mg',
            'Ibuprofen 400mg',
            'Omeprazole 20mg',
            'Antacid syrup',
            'ORS',
            'Cetirizine 10mg',
            'Loratadine 10mg',
            'Amoxicillin 500mg',
            'Azithromycin 500mg',
            'Ciprofloxacin 500mg',
            'Metronidazole 400mg',
            'Salbutamol inhaler',
            'Budesonide inhaler',
            'Multivitamin',
            'Iron + folic acid',
            'Normal saline nasal drops',
            'Chloramphenicol eye drops',
            'Diclofenac gel',
        ];
    }

    /**
     * @return list<string>
     */
    public static function doses(): array
    {
        return [
            '1 tablet',
            '1 capsule',
            '½ tablet',
            '2 tablets',
            '5 ml',
            '10 ml',
            '1 teaspoonful',
            '2 teaspoonfuls',
            '1 sachet',
            '1 drop',
            '2 drops',
            '2 puffs',
            'Apply thinly',
        ];
    }

    /**
     * @return list<string>
     */
    public static function frequencies(): array
    {
        return [
            'once daily',
            '2 times daily',
            '3 times daily',
            '4 times daily',
            'at night',
            'before breakfast',
            'after meals',
            'as needed for fever/pain',
            'as needed for wheeze',
            'after each loose motion',
        ];
    }

    /**
     * @return list<string>
     */
    public static function durations(): array
    {
        return [
            '1 day',
            '3 days',
            '5 days',
            '7 days',
            '10 days',
            '14 days',
            '30 days',
            'until diarrhoea settles',
            'until review',
        ];
    }

    /**
     * @return list<string>
     */
    public static function followUps(): array
    {
        return [
            'Return in 3 days if not improving',
            'Return in 5 days for review',
            'Return in 7 days for review',
            'Return in 2 weeks for review',
            'Return sooner if fever persists > 3 days',
            'Return urgently if chest pain / severe breathlessness',
            'Return if vomiting / diarrhoea worsens',
            'Review with lab reports',
            'Schedule follow-up with same doctor',
            'No routine follow-up unless symptoms return',
        ];
    }

    public static function formatMedicineLine(string $medicine, string $dose, string $frequency, string $duration): string
    {
        return trim(sprintf('%s — %s, %s, for %s', $medicine, $dose, $frequency, $duration));
    }
}
