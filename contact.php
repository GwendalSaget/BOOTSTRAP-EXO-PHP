<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brigade_topchef";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_reservation"])) {
    $user_id = $_SESSION["user_id"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $telephone = $_POST["telephone"];
    $date_rdv = $_POST["date_rdv"];

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($telephone) && !empty($date_rdv)) {
        $sql = "INSERT INTO reservation (user_id, nom, prenom, email, telephone, date_rdv) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $nom, $prenom, $email, $telephone, $date_rdv]);
        header("Location: contact.php");
        exit();
    }
}

if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $sql = "DELETE FROM reservation WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    header("Location: contact.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM reservation WHERE user_id = ? ORDER BY date_rdv ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Carte</title>
</head>
<body>
<div class="bg-image position-relative" style="width: 100%;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="
        background-image: url('assets/img/selune.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        opacity: 0.35;
        z-index: -1;">
    </div>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="accueil.html"><img class="logo" src ="assets/img/LogoTopChef.png" alt ="Logo du restaurant"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class=" navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link lien" href="accueil.html">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="produits.html">Carte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="contact.php">Réservation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="profile.php">Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="index.html">Déconnexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

    </header>
    <main>
        <section class="container">
            <div class="p-5">
                <h1 class="text-white text-center" style="Font-family:'GothamBook', sans-serif; font-size: 45px">
                    "Un moment, une expérience,<br>une mise en abîme des 5 sens..."
                </h1>
            </div>
            <div class="d-flex flex-column align-items-center">
                <form action="contact.php" method="post">
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="nom" placeholder="Nom" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                        <label style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Nom</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="prenom" placeholder="Prénom" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                        <label style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Prénom</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" placeholder="E-mail" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                        <label style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">E-mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="telephone" placeholder="Téléphone" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                        <label style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" >Téléphone</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="datetime-local" class="form-control" name="date_rdv" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                        <label style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" >Date et Heure</label>
                    </div>
                    <button type="submit" name="submit_reservation" class="btn bic">
                        Réserver
                    </button>
                </form>
            </div>

            <h2 class="text-center text-white mt-5">Vos réservations</h2>
            <table class="table table-dark mt-3">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date et Heure</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['nom']) ?></td>
                        <td><?= htmlspecialchars($res['prenom']) ?></td>
                        <td><?= htmlspecialchars($res['email']) ?></td>
                        <td><?= htmlspecialchars($res['telephone']) ?></td>
                        <td><?= htmlspecialchars($res['date_rdv']) ?></td>
                        <td>
                            <a href="contact.php?delete=<?= $res['id'] ?>" class="btn bic btn-sm"
                               onclick="return confirm('Supprimer cette réservation ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="text-white text-center py-3">
        <p>&copy; 2025 La Brigade TopChef M6. Tous droits réservés.</p>
    </footer>
</div>
</body>
</html>
