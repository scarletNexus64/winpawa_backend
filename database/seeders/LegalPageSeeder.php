<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legalPages = [
            [
                'type' => 'terms',
                'title' => 'Conditions d\'utilisation',
                'content' => '<div class="legal-content">
                    <h2>1. Acceptation des conditions</h2>
                    <p>En utilisant WINPAWA, vous acceptez d\'être lié par ces conditions d\'utilisation. Si vous n\'acceptez pas ces conditions, veuillez ne pas utiliser notre plateforme.</p>

                    <h2>2. Conditions d\'éligibilité</h2>
                    <p>Vous devez avoir au moins 18 ans pour utiliser WINPAWA. En créant un compte, vous confirmez que vous avez l\'âge légal pour jouer dans votre juridiction.</p>

                    <h2>3. Compte utilisateur</h2>
                    <ul>
                        <li>Vous êtes responsable de la confidentialité de votre compte</li>
                        <li>Un seul compte par personne est autorisé</li>
                        <li>Les informations fournies doivent être exactes et à jour</li>
                        <li>Vous devez nous informer immédiatement de toute utilisation non autorisée de votre compte</li>
                    </ul>

                    <h2>4. Règles de jeu</h2>
                    <ul>
                        <li>Les paris doivent être placés de bonne foi</li>
                        <li>Toute tentative de fraude entraînera la suspension du compte</li>
                        <li>Les résultats des jeux sont définitifs</li>
                        <li>WINPAWA se réserve le droit d\'annuler des paris en cas d\'erreur technique</li>
                    </ul>

                    <h2>5. Dépôts et retraits</h2>
                    <ul>
                        <li>Les dépôts minimums et maximums sont définis par méthode de paiement</li>
                        <li>Les retraits nécessitent une vérification d\'identité</li>
                        <li>Les délais de traitement varient selon la méthode choisie</li>
                        <li>Des frais peuvent s\'appliquer selon la méthode de paiement</li>
                    </ul>

                    <h2>6. Jeu responsable</h2>
                    <p>WINPAWA encourage le jeu responsable. Nous offrons des outils pour vous aider à contrôler vos habitudes de jeu, y compris des limites de dépôt et d\'auto-exclusion.</p>

                    <h2>7. Propriété intellectuelle</h2>
                    <p>Tout le contenu de WINPAWA, y compris les logos, graphiques et logiciels, est protégé par des droits d\'auteur et autres droits de propriété intellectuelle.</p>

                    <h2>8. Limitation de responsabilité</h2>
                    <p>WINPAWA ne sera pas responsable des pertes indirectes, accessoires ou consécutives résultant de l\'utilisation de notre plateforme.</p>

                    <h2>9. Modifications des conditions</h2>
                    <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications seront publiées sur cette page avec une date de mise à jour.</p>

                    <h2>10. Contact</h2>
                    <p>Pour toute question concernant ces conditions, veuillez nous contacter à support@winpawa.com</p>
                </div>',
                'is_active' => true,
            ],
            [
                'type' => 'privacy',
                'title' => 'Politique de confidentialité',
                'content' => '<div class="legal-content">
                    <h2>1. Introduction</h2>
                    <p>WINPAWA s\'engage à protéger votre vie privée. Cette politique explique comment nous collectons, utilisons et protégeons vos informations personnelles.</p>

                    <h2>2. Informations collectées</h2>
                    <h3>2.1 Informations que vous nous fournissez :</h3>
                    <ul>
                        <li>Nom complet</li>
                        <li>Adresse e-mail</li>
                        <li>Numéro de téléphone</li>
                        <li>Date de naissance</li>
                        <li>Informations de paiement</li>
                    </ul>

                    <h3>2.2 Informations collectées automatiquement :</h3>
                    <ul>
                        <li>Adresse IP</li>
                        <li>Type d\'appareil et navigateur</li>
                        <li>Historique de navigation sur notre site</li>
                        <li>Cookies et technologies similaires</li>
                    </ul>

                    <h2>3. Utilisation des informations</h2>
                    <p>Nous utilisons vos informations pour :</p>
                    <ul>
                        <li>Gérer votre compte et traiter vos transactions</li>
                        <li>Vérifier votre identité et prévenir la fraude</li>
                        <li>Améliorer nos services et votre expérience utilisateur</li>
                        <li>Vous envoyer des communications marketing (avec votre consentement)</li>
                        <li>Respecter nos obligations légales et réglementaires</li>
                    </ul>

                    <h2>4. Partage des informations</h2>
                    <p>Nous ne vendons jamais vos informations personnelles. Nous pouvons partager vos informations avec :</p>
                    <ul>
                        <li>Nos prestataires de services (paiement, vérification d\'identité)</li>
                        <li>Les autorités réglementaires lorsque la loi l\'exige</li>
                        <li>Nos partenaires marketing (uniquement avec votre consentement)</li>
                    </ul>

                    <h2>5. Sécurité des données</h2>
                    <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données :</p>
                    <ul>
                        <li>Chiffrement SSL/TLS pour toutes les transmissions</li>
                        <li>Authentification à deux facteurs disponible</li>
                        <li>Surveillance continue de la sécurité</li>
                        <li>Accès restreint aux données personnelles</li>
                    </ul>

                    <h2>6. Vos droits</h2>
                    <p>Vous avez le droit de :</p>
                    <ul>
                        <li>Accéder à vos données personnelles</li>
                        <li>Rectifier vos informations inexactes</li>
                        <li>Demander la suppression de vos données</li>
                        <li>Vous opposer au traitement de vos données</li>
                        <li>Demander la portabilité de vos données</li>
                        <li>Retirer votre consentement à tout moment</li>
                    </ul>

                    <h2>7. Cookies</h2>
                    <p>Nous utilisons des cookies pour améliorer votre expérience. Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.</p>

                    <h2>8. Conservation des données</h2>
                    <p>Nous conservons vos données aussi longtemps que nécessaire pour fournir nos services et respecter nos obligations légales.</p>

                    <h2>9. Modifications de la politique</h2>
                    <p>Nous pouvons mettre à jour cette politique de temps en temps. Les modifications importantes seront communiquées par e-mail.</p>

                    <h2>10. Contact</h2>
                    <p>Pour toute question sur cette politique ou pour exercer vos droits, contactez-nous à privacy@winpawa.com</p>
                </div>',
                'is_active' => true,
            ],
            [
                'type' => 'cookies',
                'title' => 'Politique des cookies',
                'content' => '<div class="legal-content">
                    <h2>1. Qu\'est-ce qu\'un cookie ?</h2>
                    <p>Un cookie est un petit fichier texte stocké sur votre appareil lorsque vous visitez notre site. Les cookies nous aident à améliorer votre expérience utilisateur.</p>

                    <h2>2. Types de cookies utilisés</h2>

                    <h3>2.1 Cookies essentiels</h3>
                    <p>Ces cookies sont nécessaires au fonctionnement de notre site :</p>
                    <ul>
                        <li>Cookies d\'authentification</li>
                        <li>Cookies de session</li>
                        <li>Cookies de sécurité</li>
                    </ul>

                    <h3>2.2 Cookies de performance</h3>
                    <p>Ces cookies nous aident à comprendre comment les visiteurs utilisent notre site :</p>
                    <ul>
                        <li>Google Analytics</li>
                        <li>Statistiques de navigation</li>
                    </ul>

                    <h3>2.3 Cookies de fonctionnalité</h3>
                    <p>Ces cookies permettent de mémoriser vos préférences :</p>
                    <ul>
                        <li>Langue préférée</li>
                        <li>Paramètres d\'affichage</li>
                    </ul>

                    <h3>2.4 Cookies publicitaires</h3>
                    <p>Ces cookies sont utilisés pour afficher des publicités pertinentes :</p>
                    <ul>
                        <li>Cookies de ciblage</li>
                        <li>Cookies de remarketing</li>
                    </ul>

                    <h2>3. Gestion des cookies</h2>
                    <p>Vous pouvez gérer vos préférences de cookies :</p>
                    <ul>
                        <li>Dans les paramètres de votre navigateur</li>
                        <li>Via notre outil de gestion des cookies</li>
                        <li>En refusant les cookies non essentiels</li>
                    </ul>

                    <h2>4. Impact du refus des cookies</h2>
                    <p>Le refus de certains cookies peut affecter votre expérience sur notre site et limiter certaines fonctionnalités.</p>

                    <h2>5. Cookies tiers</h2>
                    <p>Nous utilisons des services tiers qui peuvent placer leurs propres cookies :</p>
                    <ul>
                        <li>Google Analytics</li>
                        <li>Prestataires de paiement</li>
                        <li>Réseaux sociaux</li>
                    </ul>

                    <h2>6. Contact</h2>
                    <p>Pour toute question sur notre utilisation des cookies, contactez-nous à cookies@winpawa.com</p>
                </div>',
                'is_active' => true,
            ],
            [
                'type' => 'data_protection',
                'title' => 'Protection des données',
                'content' => '<div class="legal-content">
                    <h2>1. Engagement de WINPAWA</h2>
                    <p>WINPAWA s\'engage à protéger vos données personnelles conformément aux lois et règlements applicables en matière de protection des données.</p>

                    <h2>2. Base légale du traitement</h2>
                    <p>Nous traitons vos données sur la base de :</p>
                    <ul>
                        <li>Votre consentement explicite</li>
                        <li>L\'exécution d\'un contrat</li>
                        <li>Nos obligations légales</li>
                        <li>Nos intérêts légitimes</li>
                    </ul>

                    <h2>3. Mesures de sécurité</h2>

                    <h3>3.1 Mesures techniques :</h3>
                    <ul>
                        <li>Chiffrement des données sensibles (SSL/TLS)</li>
                        <li>Pare-feu et systèmes de détection d\'intrusion</li>
                        <li>Sauvegardes régulières</li>
                        <li>Mises à jour de sécurité automatiques</li>
                    </ul>

                    <h3>3.2 Mesures organisationnelles :</h3>
                    <ul>
                        <li>Formation du personnel sur la protection des données</li>
                        <li>Politiques de contrôle d\'accès strictes</li>
                        <li>Audits de sécurité réguliers</li>
                        <li>Plan de réponse aux incidents</li>
                    </ul>

                    <h2>4. Transfert de données</h2>
                    <p>Vos données sont stockées et traitées principalement au Cameroun. En cas de transfert international, nous nous assurons que des garanties appropriées sont en place.</p>

                    <h2>5. Notification de violation</h2>
                    <p>En cas de violation de données susceptible d\'affecter vos droits, nous nous engageons à vous en informer dans les plus brefs délais.</p>

                    <h2>6. Droits des personnes concernées</h2>
                    <p>Conformément à la législation, vous disposez des droits suivants :</p>
                    <ul>
                        <li>Droit d\'accès à vos données</li>
                        <li>Droit de rectification</li>
                        <li>Droit à l\'effacement (droit à l\'oubli)</li>
                        <li>Droit à la limitation du traitement</li>
                        <li>Droit à la portabilité des données</li>
                        <li>Droit d\'opposition</li>
                        <li>Droit de ne pas faire l\'objet d\'une décision automatisée</li>
                    </ul>

                    <h2>7. Exercice de vos droits</h2>
                    <p>Pour exercer vos droits, veuillez nous contacter à dpo@winpawa.com avec :</p>
                    <ul>
                        <li>Votre identité et preuve d\'identité</li>
                        <li>La nature de votre demande</li>
                        <li>Toute information pertinente</li>
                    </ul>
                    <p>Nous répondrons à votre demande dans un délai de 30 jours.</p>

                    <h2>8. Réclamation</h2>
                    <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez déposer une réclamation auprès de l\'autorité de protection des données compétente.</p>

                    <h2>9. Délégué à la protection des données</h2>
                    <p>Notre délégué à la protection des données est disponible à : dpo@winpawa.com</p>

                    <h2>10. Mise à jour</h2>
                    <p>Cette politique est régulièrement révisée pour refléter les changements dans nos pratiques et la législation.</p>
                </div>',
                'is_active' => true,
            ],
        ];

        foreach ($legalPages as $page) {
            LegalPage::updateOrCreate(
                ['type' => $page['type']],
                $page
            );
        }
    }
}
