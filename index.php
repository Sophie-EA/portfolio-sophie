<?php
declare(strict_types=1);

// 1. Connexion BDD et Template
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/Template.php';

// 2. Récupération des projets depuis la base
try {
    $query = $db->query("SELECT * FROM projects ORDER BY created_at DESC");
    $projects = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur récupération projets : " . $e->getMessage());
}

// 3. Initialisation du Template
$template = new Template(__DIR__ . '/templates/layout.php');

// 4. Titre de la page
$template->section('title');
echo 'Portfolio Sophie El Asry - Développeuse Web';
$template->endSection();

// 5. Contenu principal (tout ton HTML d'ancien.html intégré ici)
$template->section('content');
?>

<!-- SECTION HERO (Accueil) -->
<section class="accueil">
    <p class="pres">
        À travers chaque projet, je relève des défis techniques avec exigence
        et passion. <br />
        Mon objectif : concrétiser des idées utiles dans un web plus sobre et
        bien fait.
    </p>
    <img
        src="/public/images/arbre-1000x1000-2.svg"
        alt="Arbre de vie dans une main"
        class="arbre"
        id="arbre"
    />
    <div class="col3">
        <h1>
            Sophie <br />
            El Asry
        </h1>
    </div>
</section>

<!-- SECTION PROJETS (Dynamique - remplace les 8 cards statiques) -->
<section class="card-project" id="projects">
    <?php if (empty($projects)): ?>
        <p>Aucun projet à afficher pour le moment.</p>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
        <article class="card">
            <figure class="card_media">
                <img src="/public/images/<?= htmlspecialchars($project['image']) ?>" 
                     alt="Aperçu du projet <?= htmlspecialchars($project['title']) ?>" />
                <figcaption class="sr-only">Aperçu du projet <?= htmlspecialchars($project['title']) ?></figcaption>
            </figure>
            <div class="contenu">
                <h4><?= htmlspecialchars($project['title']) ?></h4>
                <p><?= htmlspecialchars($project['description']) ?></p>
                <a href="projet.php?id=<?= $project['id'] ?>" class="btn-voir">Voir le projet →</a>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- SECTION À PROPOS (Copiée depuis ancien.html) -->
<section class="aPropos" id="about">
    <article>
        <h2>À propos</h2>
        <p>
            Ancienne responsable de secteur dans l'agroalimentaire, j'ai dirigé
            des équipes, géré des urgences et accompagné mes collaborateurs au
            quotidien. Ce que j'aimais ? Être sur le terrain, résoudre des
            problèmes concrets, transmettre des compétences… et surtout, faire
            avancer les choses avec rigueur et bienveillance. Mais après un
            burn-out, j'ai pris le temps de me questionner sur ce que je voulais
            vraiment. Ce que j'aime profondément : comprendre, apprendre, créer,
            évoluer. Le développement web est vite devenu une évidence.
            Aujourd'hui en formation "Développeur Web et Web Mobile" jusqu'en
            juin 2026, je pose des bases solides : HTML, CSS, et mes premiers
            pas en JavaScript. J'avance à mon rythme, avec passion et méthode.
            J'aspire à concevoir des applications utiles, éthiques et
            responsables, à l'image de mes valeurs : le respect du vivant, le
            goût du travail bien fait et l'envie de participer à un monde plus
            résilient. Bientôt à la recherche d'un stage, je construis ce
            portfolio pour partager mon parcours, mes projets, et ma vision d'un
            web plus sobre, plus humain. Vous avez une mission, une idée, ou
            simplement envie d'échanger ? Je serais ravie d'en discuter.
        </p>
    </article>
</section>

<!-- SECTION AVIS (Avec ton SVG et les 4 cards) -->
<h2 id="titreAvis">Qu'en pense t-on?</h2>
<section class="avisClient" id="avis">
    <!-- SVG courbe de Bezier -->
    <svg
        id="BackgroundAvis"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            id="myAnimatedPath"
            d="M -70 300 Q 90 330 180 530 T 300 285 T 500 290 T 780 280 T 990 280 T 1240 270 T 1450 290 T 1690 300 T 1980 290 T 2170 280 T 2330 260 T 2540 280 T 2570 300 T 2800 260"
            stroke="var(--color1)"
            stroke-width="3"
            fill="var(--color1)"
        />
    </svg>
    <div class="contenerCardAvis">
        <article class="cardAvis">
            <img src="/public/images/prof1.jpg" alt="image profile" id="imgProfil" />
            <div class="contenuAvis">
                <h4>Nom personne</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus illo quae nesciunt ipsa temporibus illum!</p>
            </div>
        </article>
        <article class="cardAvis">
            <img src="/public/images/prof2.jpg" alt="image profile" id="imgProfil" />
            <div class="contenuAvis">
                <h4>Nom personne</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus illo quae nesciunt ipsa temporibus illum!</p>
            </div>
        </article>
        <article class="cardAvis">
            <img src="/public/images/prof3.jpg" alt="image profile" id="imgProfil" />
            <div class="contenuAvis">
                <h4>Nom personne</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus illo quae nesciunt ipsa temporibus illum!</p>
            </div>
        </article>
        <article class="cardAvis">
            <img src="/public/images/prof4.png" alt="image profile" id="imgProfil" />
            <div class="contenuAvis">
                <h4>Nom personne</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus illo quae nesciunt ipsa temporibus illum!</p>
            </div>
        </article>
    </div>
</section>

<!-- SECTION CONTACT (Formulaire + Réseaux) -->
<h4>Contact via le formulaire ou les réseaux</h4>
<section class="formContact" id="contact">
    <div class="formulaire">
        <form action="" method="POST">
            <label for="mail">Adresse e-mail </label>
            <input type="email" id="mail" name="mail" required />
            
            <label for="objet">Objet </label>
            <input type="text" id="objet" name="objet" />
            
            <label for="message">Votre message </label>
            <textarea name="message" id="message" rows="5"></textarea>
            
            <input type="submit" name="envoi" id="envoi" value="Envoyer" />
        </form>
    </div>
    <div class="reseaux">
        <a href="https://linkedin.com" target="_blank">
            <img src="/public/images/linkedin.png" alt="logo linkedin" />Sophie-EA
        </a>
        <br />
        <a href="https://github.com" target="_blank">
            <img src="/public/images/github.png" alt="logo github" />Sophie El Asry
        </a>
    </div>
</section>

<?php
$template->endSection();

// 6. Rendu final
$template->render([
    'projects' => $projects
]);
?>
