<?php
require_once 'config.php';
require_once 'functions.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Gestion des ajouts, suppressions et modifications ici
// ...

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #50b3a2;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #e8491d 3px solid;
        }
        header a {
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
        }
        header ul {
            padding: 0;
            list-style: none;
            line-height: 60px;
        }
        header li {
            float: left;
            display: inline;
            padding: 0 20px 0 20px;
        }
        .table-container {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0px 0px 10px 0px #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #50b3a2;
            color: white;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0px 0px 10px 0px #000;
        }
        input[type=text], input[type=email], input[type=password], input[type=file], select, textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type=submit] {
            width: 100%;
            background-color: #50b3a2;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type=submit]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Panel</h1>
        </div>
    </header>

    <div class="container">
        <!-- Formulaires pour ajouter des utilisateurs, des produits et des coupons -->
        <div class="form-container">
            <h2>Ajouter un utilisateur</h2>
            <form action="admin.php" method="POST">
                <label for="name">Nom:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" name="add_user" value="Ajouter">
            </form>
        </div>

        <div class="form-container">
            <h2>Ajouter un produit</h2>
            <form action="admin.php" method="POST" enctype="multipart/form-data">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="prix">Prix:</label>
                <input type="text" id="prix" name="prix" required>

                <label for="quantite_stock">Quantité en stock:</label>
                <input type="text" id="quantite_stock" name="quantite_stock" required>

                <label for="image_url">Image:</label>
                <input type="file" id="image_url" name="image_url" required>

                <label for="categorie">Catégorie:</label>
                <input type="text" id="categorie" name="categorie">

                <label for="marque">Marque:</label>
                <input type="text" id="marque" name="marque">

                <input type="submit" name="add_product" value="Ajouter">
            </form>
        </div>

        <div class="form-container">
            <h2>Ajouter un coupon</h2>
            <form action="admin.php" method="POST">
                <label for="code">Code:</label>
                <input type="text" id="code" name="code" required>

                <label for="discount_percentage">Pourcentage de réduction:</label>
                <input type="text" id="discount_percentage" name="discount_percentage" required>

                <label for="expiry_date">Date d'expiration:</label>
                <input type="date" id="expiry_date" name="expiry_date">

                <label for="usage_limit">Limite d'utilisation:</label>
                <input type="text" id="usage_limit" name="usage_limit">

                <input type="submit" name="add_coupon" value="Ajouter">
            </form>
        </div>

        <!-- Affichage des données des tables -->
        <div class="table-container">
            <h2>Utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Vérifié</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM users");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['is_verified']}</td>
                                <td><a href='admin.php?delete_user={$row['id']}'>Supprimer</a></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Produits</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Catégorie</th>
                        <th>Marque</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM produits");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['nom']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['prix']}</td>
                                <td>{$row['quantite_stock']}</td>
                                <td><img src='{$row['image_url']}' alt='{$row['nom']}' width='50'></td>
                                <td>{$row['categorie']}</td>
                                <td>{$row['marque']}</td>
                                <td><a href='admin.php?delete_product={$row['id']}'>Supprimer</a></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Coupons</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Réduction</th>
                        <th>Date d'expiration</th>
                        <th>Limite</th>
                        <th>Utilisé</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM coupons");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['code']}</td>
                                <td>{$row['discount_percentage']}%</td>
                                <td>{$row['expiry_date']}</td>
                                <td>{$row['usage_limit']}</td>
                                <td>{$row['times_used']}</td>
                                <td><a href='admin.php?delete_coupon={$row['id']}'>Supprimer</a></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Gestion des soumissions de formulaires

    // Ajouter un utilisateur
    if (isset($_POST['add_user'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo "Nouvel utilisateur ajouté avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    // Ajouter un produit
    if (isset($_POST['add_product'])) {
        $nom = $_POST['nom'];
        $description = $_POST['description'];
        $prix = $_POST['prix'];
        $quantite_stock = $_POST['quantite_stock'];
        $categorie = $_POST['categorie'];
        $marque = $_POST['marque'];

        $target_dir = "img_prod/";
        $target_file = $target_dir . basename($_FILES["image_url"]["name"]);
        move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file);

        $sql = "INSERT INTO produits (nom, description, prix, quantite_stock, image_url, categorie, marque) VALUES ('$nom', '$description', '$prix', '$quantite_stock', '$target_file', '$categorie', '$marque')";
        if ($conn->query($sql) === TRUE) {
            echo "Nouveau produit ajouté avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    // Ajouter un coupon
    if (isset($_POST['add_coupon'])) {
        $code = $_POST['code'];
        $discount_percentage = $_POST['discount_percentage'];
        $expiry_date = $_POST['expiry_date'];
        $usage_limit = $_POST['usage_limit'];

        $sql = "INSERT INTO coupons (code, discount_percentage, expiry_date, usage_limit) VALUES ('$code', '$discount_percentage', '$expiry_date', '$usage_limit')";
        if ($conn->query($sql) === TRUE) {
            echo "Nouveau coupon ajouté avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    // Supprimer un utilisateur
    if (isset($_GET['delete_user'])) {
        $id = $_GET['delete_user'];
        $sql = "DELETE FROM users WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Utilisateur supprimé avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    // Supprimer un produit
    if (isset($_GET['delete_product'])) {
        $id = $_GET['delete_product'];
        $sql = "DELETE FROM produits WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Produit supprimé avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    // Supprimer un coupon
    if (isset($_GET['delete_coupon'])) {
        $id = $_GET['delete_coupon'];
        $sql = "DELETE FROM coupons WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Coupon supprimé avec succès.";
        } else {
            echo "Erreur : " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>
</body>
</html>
