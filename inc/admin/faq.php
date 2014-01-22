<?php defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) ); ?>

<h2>Ma clé ne fonctionne pas</h2>
<p>Vérifiez l'orthographe du site dans votre profil sur le site de WP Rocket.</p>
<p>Si c'est correct, vérifiez alors que le site entré est bien le même que celui dans les options de Wordpress et non une redirection.</p>
<p>Exemple avec le site example.com dans le paramétrage WordPress : S'il redirige sur le .fr, et que vous auriez entré le .fr dans votre compte WP Rocket, alors il y a un soucis.</p>
<p>Les deux valeurs étant différentes, la clé n'est pas valide pour le .com mais pour le .fr</p>

<h2>Pour combien de sites ma clé est-elle valide ?</h2>
<p>Une clé valide 1 nom de domaine. Si vous avez example.com, cela fait 1 clé.</p>
<p>Si vous avez example.com, ww.example.com, dev.example.com, demo.example.com, example.com/dev/, example.com/demo/ tous ces sites dépendent de la même clé WP Rocket.</p>
<p>Il vous faudra donc une clé supplémentaire pour chaque autre nom de domaine différent de example.com</p>

<h2>Que fait exactement WP Rocket ? </h2>
<p>WP Rocket est un plugin de cache complet qui embarque de nombreuses fonctionnalités :</p>
<ul>
	<li>Mise en cache de l'ensemble des pages pour un affichage rapide</li>
	<li>Préchargement des fichiers de cache à l'aide de 2 robots en Python</li>
	<li>Réduction du nombres de requêtes HTTP pour réduire le temps de chargement</li>
	<li>Diminution de la bande passante grâce à la compression GZIP</li>
	<li>Gestion des headers (expire, etags, etc...)</li>
	<li>Minification et concaténation des fichiers JS et CSS</li>
	<li>Chargement différé des images (LazyLoad)</li>
	<li>Chargement différé des fichiers JavaScript</li>
	<li>Optimisation des images</li>
</ul>

<h2>Je n'ai activé aucune des options de base, est-ce que WP Rocket fonctionne ?</h2>
<p>Oui.</p>
<p>Les options de base sont des optimisations complémentaires que l’on peut qualifier de bonus. Ces options ne sont pas indispensables pour améliorer le temps de chargement de votre site Internet.</p>
<p>Quelque soit votre configuration de WP Rocket, les fonctionnalités suivantes seront toujours actives :</p>
<ul>
	<li>Mise en cache de l'ensemble des pages pour affichage rapide</li>
	<li>Diminution de la bande passante grâce à la compression GZIP</li>
	<li>Gestion des headers (expire, etags, etc)</li>
	<li>Optimisation des images</li>
</ul>

<h2>Que dois-je faire en cas de problème lié à WP Rocket que je n’arrive pas à résoudre ?</h2>
<p>Si aucune des réponses de la F.A.Q. présente ci-dessous apporte une réponse à votre problématique, vous pouvez nous faire part de votre problème sur notre <a href="http://support.wp-rocket.me" target="_blank">support</a>. Nous vous répondrons dans les plus brefs délais.</p>

<h2>Ma licence est expirée, que dois-je faire ?</h2>
<p>Pas de panique, WP Rocket continuera de fonctionner sans problème. Vous recevrez un mail vous indiquant que votre licence va bientôt arriver à expiration. Vous trouverez un lien de renouvellement qui sera actif même après l’expiration.</p>

<h2>Je souhaite modifier l'URL de mon site associé à ma licence, que dois-je faire ?</h2>
<p>Vous devez nous contacter par mail (<a href="mailto:contact@wp-rocket.me">contact@wp-rocket.me</a>) en nous indiquant la raison de votre modification. Si acceptée, la modification sera réalisée par l’équipe de WP Rocket.</p>

<h2>Quels outils dois-je utiliser pour mesurer les performances de mon site ?</h2>
<p>Vous pouvez mesurer les performances de votre site Internet à l’aide des outils suivants : </p>
<ul>
	<li><a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a></li>
	<li><a href="http://gtmetrix.com/" target="_blank">GT Metrix</a></li>
	<li><a href="http://www.webpagetest.org/" target="_blank">Webpagetest</a></li>
</ul>

<p>Ces 2 derniers donnent deux indications :</p>
<ul>
	<li>une note globale des bonnes pratiques à appliquer</li>
	<li>un temps de chargement</li>
</ul>

<p>Ces données sont indicatives et ne reflètent pas forcément la vitesse d’affichage réelle de votre site Internet.</p>

<p>Pour réaliser des tests de temps de chargement plus proche de la réalité nous conseillons d’utiliser <a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a> avec l’option <code>Amsterdam</code> comme serveur.</p>

<h2>WP Rocket fonctionne-t-il avec les permaliens par défaut ?</h2>
<p>Non.</p>

<p>Il est nécessaire d'avoir des permaliens personnalisés du type <code>http://example.com/mon-article/</code> plutôt que <code>http://example.com/?p=1234</code>.</p>

<h2>Avec quels serveurs Web WP Rocket est-il compatible ?</h2>
<p>WP Rocket est compatible avec les serveurs Web <strong>Apache</strong>. Pour le moment, WP Rocket n’est donc pas compatible avec les serveurs Web NGINX et Litepseed.</p>

<h2>Le rapport PageSpeed ou YSlow m’indique que le contenu n’est pas gzipé et/ou n’a pas d’expiration, que dois-je faire ?</h2>

<p>WP Rocket ajoute automatiquement les bonnes règles d’expirations et de gzip des fichiers statiques. Si elles ne sont pas appliquées, il est possible qu’un plugin entre en conflit (exemple: <a href="http://wordpress.org/plugins/wp-retina-2x/" target="_blank">WP Retina 2x</a>). Essayez de désactiver temporairement tous les plugins, excepté WP Rocket, et de refaire le test.</p>

<p>Si cela n’est pas concluant, cela signifie que le <code>mod_expire</code> et/ou <code>mod_deflate</code> n’est pas activé sur votre serveur.</p>

<h2>WP Rocket est-il compatible avec les autres plugins de cache, tels que WP Super Cache ou W3 Total Cache ?</h2>
<p>Non.</p>

<p>Il est impératif de <strong>supprimer tous les autres plugins d'optimisation</strong> (cache, minification, LazyLoad) AVANT l’activation de WP Rocket.</p>

<h2>WP Rocket est-il compatible avec WP Touch, WordPress Mobile Pack et WP Mobile Detector ?</h2>
<p>Oui.</p>
<p>Par contre, dans les options de base, vous devez décocher la case <code>Activer la mise en cache pour les appareils mobile</code>.</p>

<h2>WP Rocket est-il compatible avec WooCommerce ?</h2>
<p>Oui.</p>

<p>Cependant, il faut exclure les pages panier et commande de la mise en cache. Cela se fait à partir de l’option avancée <code>Ne jamais mettre en cache les pages suivantes</code> et en ajoutant les valeurs suivantes :</p>
<p><code>/panier/<br/>
/commande/(.*)
</code></p>

<h2>WP Rocket est-il compatible avec WPML ?</h2>
<p>Oui.</p>
<p>Vous avez même la possibilité de vider/précharger la cache d'une langue précise ou de toutes les langues en même temps.</p>

<h2>En quoi consiste la minification et concaténation des fichiers ?</h2>
<p>La minification consiste à supprimer tous les éléments superflus d’une fichier HTML, CSS ou JavaScript : espaces, commentaires, etc... Cela permet de diminuer la taille des fichiers. Ainsi, les navigateurs lisent plus rapidement les fichiers.</p>

<p>La concaténation consiste à regrouper en un seul, un ensemble de fichiers. Cela a pour effet de diminuer le nombre de requêtes HTTP.</p>
<h2>Que dois-je faire si WP Rocket déforme l’affichage de mon site ?</h2>
<p>Il y a de fortes chances que la déformation soit provoquée par la minification des fichiers HTML, CSS et/ou JavaScript. Pour résoudre le problème, nous conseillons de regarder la vidéo suivante : <a href="http://www.youtube.com/embed/iziXSvZgxLk" class="fancybox">http://www.youtube.com/embed/iziXSvZgxLk</a>.</p>

<h2>À quel intervalle le cache est mis à jour ?</h2>
<p>Le cache est automatiquement rafraîchit à chaque mise à jour d'un contenu (ajout/édition/suppression d’un article, publication d’un commentaire, etc...).</p>
<p>Dans les options de base, vous pouvez aussi spécifier un délai de purge automatique du cache.</p>

<h2>Comment ne pas mettre en cache une page particulière ?</h2>
<p>Dans les options avancées, il est possible de spécifier des URLs à ne pas mettre en cache. Pour cela, il faut ajouter dans le champ de saisie <code>Ne jamais mettre en cache les pages suivantes</code> les URLs à exclure.</p>

<h2>Comment fonctionne les robots de préchargement des fichiers de cache ?</h2>
<p>Pour mettre une page en cache, il faut un premier visiteur. Pour éviter qu’un premier visiteur le fasse, nous avons développé deux robots (en python) qui crawl les pages de votre site Internet.</p>

<p>Le premier va visiter votre site à la demande à l’aide du bouton “Précharger le cache”. Le second va automatiquement visiter votre site dès que vous allez créer/éditer/supprimer un article.</p>

<p>Pour plus d’informations, vous pouvez consulter notre vidéo à ce propos : <a href="http://www.youtube.com/embed/9jDcg2f-9yM" class="fancybox">http://www.youtube.com/embed/9jDcg2f-9yM</a>.</p>