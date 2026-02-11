<?php

namespace App\Constants;

class Specialty
{
    public const ANATOMIE = 'Anatomie';
    public const ANATOMIE_CYTOLOGIE_PATHOLOGIQUE = 'Anatomie et cytologie pathologique';
    public const ANESTHESIE_REANIMATION = 'Anesthésie réanimation';
    public const BIOLOGIE_MEDICALE = 'Biologie médicale';
    public const BIOLOGIE_MEDICALE_BIOCHIMIE = 'Biologie médicale option biochimie';
    public const BIOLOGIE_MEDICALE_HEMATOLOGIE = 'Biologie médicale option hématologie';
    public const BIOLOGIE_MEDICALE_IMMUNOLOGIE = 'Biologie médicale option immunologie';
    public const BIOLOGIE_MEDICALE_MICROBIOLOGIE = 'Biologie médicale option microbiologie';
    public const BIOLOGIE_MEDICALE_PARASITOLOGIE = 'Biologie médicale option parasitologie';
    public const BIOPHYSIQUE_MEDECINE_NUCLEAIRE = 'Biophysique et médecine nucléaire';
    public const CARCINOLOGIE_MEDICALE = 'Carcinologie médicale';
    public const CARDIOLOGIE = 'Cardiologie';
    public const CHIRURGIE_CARDIO_VASCULAIRE = 'Chirurgie cardio vasculaire';
    public const CHIRURGIE_CARCINOLOGIQUE = 'Chirurgie carcinologique';
    public const CHIRURGIE_GENERALE = 'Chirurgie générale';
    public const CHIRURGIE_NEUROLOGIQUE = 'Chirurgie neurologique';
    public const CHIRURGIE_ORTHOPEDIQUE_TRAUMATOLOGIQUE = 'Chirurgie orthopédique et traumatologique';
    public const CHIRURGIE_PEDIATRIQUE = 'Chirurgie pédiatrique';
    public const CHIRURGIE_PLASTIQUE_REPARATRICE_ESTHETIQUE = 'Chirurgie plastique réparatrice et esthétique';
    public const CHIRURGIE_THORACIQUE = 'Chirurgie thoracique';
    public const CHIRURGIE_UROLOGIQUE = 'Chirurgie urologique';
    public const CHIRURGIE_VASCULAIRE_PERIPHERIQUE = 'Chirurgie vasculaire périphérique';
    public const DERMATOLOGIE = 'Dermatologie';
    public const ENDOCRINOLOGIE = 'Endocrinologie';
    public const GASTRO_ENTEROLOGIE = 'Gastro-entérologie';
    public const GENETIQUE = 'Génétique';
    public const GYNECOLOGIE_OBSTETRIQUE = 'Gynécologie obstétrique';
    public const HEMATOLOGIE_CLINIQUE = 'Hématologie clinique';
    public const HISTO_EMBRYOLOGIE = 'Histo-embryologie';
    public const IMAGERIE_MEDICALE = 'Imagerie médicale';
    public const MALADIES_INFECTIEUSES = 'Maladies infectieuses';
    public const MEDECINE_AERONAUTIQUE_SPATIALE = 'Médecine aéronautique et spatiale';
    public const MEDECINE_DE_FAMILLE = 'Médecine de Famille';
    public const MEDECINE_URGENCE = 'Médecine d\'urgence';
    public const MEDECINE_DU_TRAVAIL = 'Médecine du travail';
    public const MEDECINE_GENERALE = 'Médecine générale';
    public const MEDECINE_INTERNE = 'Médecine interne';
    public const MEDECINE_LEGALE = 'Médecine légale';
    public const MEDECINE_PHYSIQUE_READAPTATION = 'Médecine physique, rééducation et réadaptation fonctionnelle';
    public const MEDECINE_PREVENTIVE_COMMUNAUTAIRE = 'Médecine préventive et communautaire';
    public const NEPHROLOGIE = 'Néphrologie';
    public const NEUROLOGIE = 'Neurologie';
    public const NUTRITION_MALADIES_NUTRITIONNELLES = 'Nutrition et maladies nutritionnelles';
    public const OPHTALMOLOGIE = 'Ophtalmologie';
    public const OTO_RHINO_LARYNGOLOGIE = 'Oto-rhino-laryngologie';
    public const PEDIATRIE = 'Pédiatrie';
    public const PEDO_PSYCHIATRIE = 'Pédo psychiatrie';
    public const PHARMACOLOGIE = 'Pharmacologie';
    public const PHYSIOLOGIE_EXPLORATION_FONCTIONNELLE = 'Physiologie et exploration fonctionnelle';
    public const PNEUMOLOGIE = 'Pneumologie';
    public const PSYCHIATRIE = 'Psychiatrie';
    public const RADIOTHERAPIE_CARCINOLOGIQUE = 'Radiothérapie carcinologique';
    public const REANIMATION_MEDICALE = 'Réanimation médicale';
    public const RHUMATOLOGIE = 'Rhumatologie';
    public const SANS_SPECIALITE = 'sans spécialité';
    public const SPECIALISTE_MEDECINE_FAMILLE = 'Spécialiste en médecine de famille';
    public const STOMATOLOGIE_CHIRURGIE_MAXILLO_FACIALE = 'Stomatologie et chirurgie maxillo-faciale';
    public const UROLOGIE = 'Urologie';
    public const DENTISTE = 'Dentiste';

    public static function getChoices(): array
    {
        return [
            'Anatomie' => self::ANATOMIE,
            'Anatomie et cytologie pathologique' => self::ANATOMIE_CYTOLOGIE_PATHOLOGIQUE,
            'Anesthésie réanimation' => self::ANESTHESIE_REANIMATION,
            'Biologie médicale' => self::BIOLOGIE_MEDICALE,
            'Biologie médicale option biochimie' => self::BIOLOGIE_MEDICALE_BIOCHIMIE,
            'Biologie médicale option hématologie' => self::BIOLOGIE_MEDICALE_HEMATOLOGIE,
            'Biologie médicale option immunologie' => self::BIOLOGIE_MEDICALE_IMMUNOLOGIE,
            'Biologie médicale option microbiologie' => self::BIOLOGIE_MEDICALE_MICROBIOLOGIE,
            'Biologie médicale option parasitologie' => self::BIOLOGIE_MEDICALE_PARASITOLOGIE,
            'Biophysique et médecine nucléaire' => self::BIOPHYSIQUE_MEDECINE_NUCLEAIRE,
            'Carcinologie médicale' => self::CARCINOLOGIE_MEDICALE,
            'Cardiologie' => self::CARDIOLOGIE,
            'Chirurgie cardio vasculaire' => self::CHIRURGIE_CARDIO_VASCULAIRE,
            'Chirurgie carcinologique' => self::CHIRURGIE_CARCINOLOGIQUE,
            'Chirurgie générale' => self::CHIRURGIE_GENERALE,
            'Chirurgie neurologique' => self::CHIRURGIE_NEUROLOGIQUE,
            'Chirurgie orthopédique et traumatologique' => self::CHIRURGIE_ORTHOPEDIQUE_TRAUMATOLOGIQUE,
            'Chirurgie pédiatrique' => self::CHIRURGIE_PEDIATRIQUE,
            'Chirurgie plastique réparatrice et esthétique' => self::CHIRURGIE_PLASTIQUE_REPARATRICE_ESTHETIQUE,
            'Chirurgie thoracique' => self::CHIRURGIE_THORACIQUE,
            'Chirurgie urologique' => self::CHIRURGIE_UROLOGIQUE,
            'Chirurgie vasculaire périphérique' => self::CHIRURGIE_VASCULAIRE_PERIPHERIQUE,
            'Dermatologie' => self::DERMATOLOGIE,
            'Endocrinologie' => self::ENDOCRINOLOGIE,
            'Gastro-entérologie' => self::GASTRO_ENTEROLOGIE,
            'Génétique' => self::GENETIQUE,
            'Gynécologie obstétrique' => self::GYNECOLOGIE_OBSTETRIQUE,
            'Hématologie clinique' => self::HEMATOLOGIE_CLINIQUE,
            'Histo-embryologie' => self::HISTO_EMBRYOLOGIE,
            'Imagerie médicale' => self::IMAGERIE_MEDICALE,
            'Maladies infectieuses' => self::MALADIES_INFECTIEUSES,
            'Médecine aéronautique et spatiale' => self::MEDECINE_AERONAUTIQUE_SPATIALE,
            'Médecine de Famille' => self::MEDECINE_DE_FAMILLE,
            'Médecine d\'urgence' => self::MEDECINE_URGENCE,
            'Médecine du travail' => self::MEDECINE_DU_TRAVAIL,
            'Médecine générale' => self::MEDECINE_GENERALE,
            'Médecine interne' => self::MEDECINE_INTERNE,
            'Médecine légale' => self::MEDECINE_LEGALE,
            'Médecine physique, rééducation et réadaptation fonctionnelle' => self::MEDECINE_PHYSIQUE_READAPTATION,
            'Médecine préventive et communautaire' => self::MEDECINE_PREVENTIVE_COMMUNAUTAIRE,
            'Néphrologie' => self::NEPHROLOGIE,
            'Neurologie' => self::NEUROLOGIE,
            'Nutrition et maladies nutritionnelles' => self::NUTRITION_MALADIES_NUTRITIONNELLES,
            'Ophtalmologie' => self::OPHTALMOLOGIE,
            'Oto-rhino-laryngologie' => self::OTO_RHINO_LARYNGOLOGIE,
            'Pédiatrie' => self::PEDIATRIE,
            'Pédo psychiatrie' => self::PEDO_PSYCHIATRIE,
            'Pharmacologie' => self::PHARMACOLOGIE,
            'Physiologie et exploration fonctionnelle' => self::PHYSIOLOGIE_EXPLORATION_FONCTIONNELLE,
            'Pneumologie' => self::PNEUMOLOGIE,
            'Psychiatrie' => self::PSYCHIATRIE,
            'Radiothérapie carcinologique' => self::RADIOTHERAPIE_CARCINOLOGIQUE,
            'Réanimation médicale' => self::REANIMATION_MEDICALE,
            'Rhumatologie' => self::RHUMATOLOGIE,
            'sans spécialité' => self::SANS_SPECIALITE,
            'Spécialiste en médecine de famille' => self::SPECIALISTE_MEDECINE_FAMILLE,
            'Stomatologie et chirurgie maxillo-faciale' => self::STOMATOLOGIE_CHIRURGIE_MAXILLO_FACIALE,
            'Urologie' => self::UROLOGIE,
            'Dentiste' => self::DENTISTE,
        ];
    }

    public static function getDisplayName(string $specialty): string
    {
        $choices = self::getChoices();
        $flipped = array_flip($choices);

        return $flipped[$specialty] ?? $specialty;
    }

    public static function getGroups(): array
    {
        return [
            'Spécialités médicales' => [
                self::ANATOMIE,
                self::ANATOMIE_CYTOLOGIE_PATHOLOGIQUE,
                self::ANESTHESIE_REANIMATION,
                self::CARCINOLOGIE_MEDICALE,
                self::CARDIOLOGIE,
                self::DERMATOLOGIE,
                self::ENDOCRINOLOGIE,
                self::GASTRO_ENTEROLOGIE,
                self::GENETIQUE,
                self::GYNECOLOGIE_OBSTETRIQUE,
                self::HEMATOLOGIE_CLINIQUE,
                self::HISTO_EMBRYOLOGIE,
                self::IMAGERIE_MEDICALE,
                self::MALADIES_INFECTIEUSES,
                self::MEDECINE_AERONAUTIQUE_SPATIALE,
                self::MEDECINE_DE_FAMILLE,
                self::MEDECINE_URGENCE,
                self::MEDECINE_DU_TRAVAIL,
                self::MEDECINE_GENERALE,
                self::MEDECINE_INTERNE,
                self::MEDECINE_LEGALE,
                self::MEDECINE_PHYSIQUE_READAPTATION,
                self::MEDECINE_PREVENTIVE_COMMUNAUTAIRE,
                self::NEPHROLOGIE,
                self::NEUROLOGIE,
                self::NUTRITION_MALADIES_NUTRITIONNELLES,
                self::OPHTALMOLOGIE,
                self::OTO_RHINO_LARYNGOLOGIE,
                self::PEDIATRIE,
                self::PEDO_PSYCHIATRIE,
                self::PHARMACOLOGIE,
                self::PHYSIOLOGIE_EXPLORATION_FONCTIONNELLE,
                self::PNEUMOLOGIE,
                self::PSYCHIATRIE,
                self::RADIOTHERAPIE_CARCINOLOGIQUE,
                self::REANIMATION_MEDICALE,
                self::RHUMATOLOGIE,
                self::SANS_SPECIALITE,
                self::SPECIALISTE_MEDECINE_FAMILLE,
                self::STOMATOLOGIE_CHIRURGIE_MAXILLO_FACIALE,
                self::UROLOGIE,
            ],
            'Spécialités chirurgicales' => [
                self::CHIRURGIE_CARDIO_VASCULAIRE,
                self::CHIRURGIE_CARCINOLOGIQUE,
                self::CHIRURGIE_GENERALE,
                self::CHIRURGIE_NEUROLOGIQUE,
                self::CHIRURGIE_ORTHOPEDIQUE_TRAUMATOLOGIQUE,
                self::CHIRURGIE_PEDIATRIQUE,
                self::CHIRURGIE_PLASTIQUE_REPARATRICE_ESTHETIQUE,
                self::CHIRURGIE_THORACIQUE,
                self::CHIRURGIE_UROLOGIQUE,
                self::CHIRURGIE_VASCULAIRE_PERIPHERIQUE,
            ],
            'Biologie et laboratoire' => [
                self::BIOLOGIE_MEDICALE,
                self::BIOLOGIE_MEDICALE_BIOCHIMIE,
                self::BIOLOGIE_MEDICALE_HEMATOLOGIE,
                self::BIOLOGIE_MEDICALE_IMMUNOLOGIE,
                self::BIOLOGIE_MEDICALE_MICROBIOLOGIE,
                self::BIOLOGIE_MEDICALE_PARASITOLOGIE,
                self::BIOPHYSIQUE_MEDECINE_NUCLEAIRE,
            ],
            'Dentisterie' => [
                self::DENTISTE,
            ],
        ];
    }
}
