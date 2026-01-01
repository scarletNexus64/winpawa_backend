<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'type' => 'privacy',
                'title' => 'Politique de Confidentialité',
                'content' => '<h2>1. Introduction</h2><p>WINPAWA accorde une grande importance à la protection de vos données personnelles. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations.</p><h2>2. Données collectées</h2><p>Nous collectons les données suivantes :</p><ul><li>Nom et prénom</li><li>Adresse email</li><li>Numéro de téléphone</li><li>Informations de paiement</li><li>Historique des transactions</li><li>Données de navigation</li></ul><h2>3. Utilisation des données</h2><p>Vos données sont utilisées pour :</p><ul><li>Gérer votre compte utilisateur</li><li>Traiter vos transactions</li><li>Améliorer nos services</li><li>Vous envoyer des notifications importantes</li><li>Respecter nos obligations légales</li></ul><h2>4. Protection des données</h2><p>Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos données contre tout accès non autorisé, modification, divulgation ou destruction.</p><h2>5. Vos droits</h2><p>Vous disposez des droits suivants :</p><ul><li>Droit d\'accès à vos données</li><li>Droit de rectification</li><li>Droit à l\'effacement</li><li>Droit à la portabilité</li><li>Droit d\'opposition</li></ul><h2>6. Contact</h2><p>Pour toute question concernant cette politique, contactez-nous à : contact@winpawa.com</p>',
            ],
            [
                'type' => 'terms',
                'title' => 'Conditions Générales d\'Utilisation',
                'content' => '<h2>1. Objet</h2><p>Les présentes Conditions Générales d\'Utilisation (CGU) ont pour objet de définir les modalités et conditions d\'utilisation de la plateforme WINPAWA.</p><h2>2. Acceptation des CGU</h2><p>En utilisant WINPAWA, vous acceptez sans réserve les présentes CGU. Si vous n\'acceptez pas ces conditions, veuillez ne pas utiliser notre service.</p><h2>3. Inscription</h2><p>L\'inscription à WINPAWA est réservée aux personnes âgées de 18 ans et plus. Vous devez fournir des informations exactes et complètes lors de votre inscription.</p><h2>4. Compte utilisateur</h2><p>Vous êtes responsable de la confidentialité de vos identifiants de connexion. Toute utilisation de votre compte est présumée être effectuée par vous.</p><h2>5. Services proposés</h2><p>WINPAWA propose une plateforme de jeux et de paris. Les services sont fournis "en l\'état" sans garantie d\'aucune sorte.</p><h2>6. Transactions financières</h2><p>Les dépôts et retraits sont soumis à nos politiques de paiement. Nous nous réservons le droit de refuser ou d\'annuler toute transaction suspecte.</p><h2>7. Jeu responsable</h2><p>WINPAWA encourage le jeu responsable. Si vous rencontrez des problèmes de dépendance, veuillez contacter nos services d\'assistance.</p><h2>8. Interdictions</h2><p>Il est strictement interdit de :</p><ul><li>Utiliser le service à des fins frauduleuses</li><li>Créer plusieurs comptes</li><li>Manipuler les résultats des jeux</li><li>Utiliser des bots ou logiciels automatisés</li></ul><h2>9. Suspension et résiliation</h2><p>Nous nous réservons le droit de suspendre ou résilier votre compte en cas de violation des présentes CGU.</p><h2>10. Modifications</h2><p>WINPAWA se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés des modifications importantes.</p>',
            ],
            [
                'type' => 'cookies',
                'title' => 'Politique des Cookies',
                'content' => '<h2>1. Qu\'est-ce qu\'un cookie ?</h2><p>Un cookie est un petit fichier texte stocké sur votre appareil lorsque vous visitez notre site. Les cookies nous aident à améliorer votre expérience utilisateur.</p><h2>2. Types de cookies utilisés</h2><h3>Cookies essentiels</h3><p>Ces cookies sont nécessaires au fonctionnement du site. Ils incluent les cookies de session et d\'authentification.</p><h3>Cookies de performance</h3><p>Ces cookies nous permettent d\'analyser l\'utilisation du site pour améliorer nos services.</p><h3>Cookies de fonctionnalité</h3><p>Ces cookies mémorisent vos préférences (langue, devise, etc.).</p><h3>Cookies publicitaires</h3><p>Ces cookies sont utilisés pour vous proposer des publicités pertinentes.</p><h2>3. Gestion des cookies</h2><p>Vous pouvez gérer les cookies via les paramètres de votre navigateur. Notez que la désactivation de certains cookies peut affecter le fonctionnement du site.</p><h2>4. Cookies tiers</h2><p>Nous utilisons des services tiers (Google Analytics, etc.) qui peuvent placer leurs propres cookies sur votre appareil.</p><h2>5. Durée de conservation</h2><p>Les cookies de session sont supprimés à la fermeture de votre navigateur. Les cookies persistants restent jusqu\'à leur expiration ou jusqu\'à ce que vous les supprimiez.</p><h2>6. Consentement</h2><p>En utilisant notre site, vous consentez à l\'utilisation de cookies conformément à cette politique.</p>',
            ],
            [
                'type' => 'data_protection',
                'title' => 'Protection des Données Personnelles',
                'content' => '<h2>1. Responsable du traitement</h2><p>WINPAWA est responsable du traitement de vos données personnelles conformément au RGPD.</p><h2>2. Base légale du traitement</h2><p>Le traitement de vos données repose sur :</p><ul><li>L\'exécution d\'un contrat (gestion de votre compte)</li><li>Votre consentement (marketing)</li><li>Nos obligations légales (lutte anti-blanchiment)</li><li>Notre intérêt légitime (amélioration des services)</li></ul><h2>3. Destinataires des données</h2><p>Vos données peuvent être communiquées à :</p><ul><li>Nos prestataires de services (hébergement, paiement)</li><li>Les autorités légales sur demande</li><li>Nos partenaires commerciaux (avec votre consentement)</li></ul><h2>4. Transferts internationaux</h2><p>Vos données peuvent être transférées hors de l\'Union Européenne. Dans ce cas, nous garantissons un niveau de protection adéquat.</p><h2>5. Durée de conservation</h2><p>Nous conservons vos données :</p><ul><li>Pendant la durée de votre compte actif</li><li>3 ans après la fermeture du compte (obligations légales)</li><li>5 ans pour les données financières</li></ul><h2>6. Sécurité des données</h2><p>Nous utilisons :</p><ul><li>Chiffrement SSL/TLS</li><li>Authentification à deux facteurs</li><li>Sauvegardes régulières</li><li>Contrôles d\'accès stricts</li></ul><h2>7. Vos droits RGPD</h2><p>Conformément au RGPD, vous disposez des droits suivants :</p><ul><li>Droit d\'accès (Article 15)</li><li>Droit de rectification (Article 16)</li><li>Droit à l\'effacement (Article 17)</li><li>Droit à la limitation (Article 18)</li><li>Droit à la portabilité (Article 20)</li><li>Droit d\'opposition (Article 21)</li><li>Droit de retirer votre consentement</li></ul><h2>8. Exercer vos droits</h2><p>Pour exercer vos droits, contactez notre DPO à : dpo@winpawa.com</p><h2>9. Réclamation</h2><p>Vous avez le droit de déposer une réclamation auprès de la CNIL : www.cnil.fr</p><h2>10. Mises à jour</h2><p>Cette politique peut être mise à jour. La date de dernière modification est indiquée en bas de page.</p>',
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::updateOrCreate(
                ['type' => $page['type']],
                $page
            );
        }
    }
}
