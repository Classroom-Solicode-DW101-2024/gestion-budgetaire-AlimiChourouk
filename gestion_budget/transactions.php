<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une transaction - FarhaEvents</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            font-weight: normal;
            margin-bottom: 0;
        }

        input[type="number"],
        input[type="text"],
        input[type="date"],
        select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        select {
            appearance: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 10px center;
            background-size: 12px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .error ul {
            list-style: none;
            padding: 0;
        }

        .success {
            color: #4CAF50;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 20px;
            }

            input[type="number"],
            input[type="text"],
            input[type="date"],
            select,
            input[type="submit"] {
                font-size: 14px;
                padding: 10px;
            }

            .checkbox-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Ajouter une transaction</h2>
        <form action="" method="POST">
            <div class="checkbox-group">
                <label>Type de transaction</label>
                <div>
                    <input type="checkbox" name="type_transaction" id="depense" value="depense">
                    <label for="depense">Dépense</label>
                </div>
                <div>
                    <input type="checkbox" name="type_transaction" id="revenu" value="revenu">
                    <label for="revenu">Revenu</label>
                </div>
            </div>
            <label for="montant">Montant</label>
            <input type="number" min="1" name="montant" id="montant" placeholder="Entrez votre montant" required>
            <label for="categorie">Catégorie</label>
            <select name="categorie" id="categorie" required>
                <optgroup label="Revenu">
                    <option value="Salaire">Salaire</option>
                    <option value="Bourse">Bourse</option>
                    <option value="Ventes">Ventes</option>
                    <option value="AutresRevenu">Autres</option>
                </optgroup>
                <optgroup label="Dépense">
                    <option value="Logement">Logement</option>
                    <option value="Transport">Transport</option>
                    <option value="Alimentation">Alimentation</option>
                    <option value="Santé">Santé</option>
                    <option value="Divertissement">Divertissement</option>
                    <option value="Éducation">Éducation</option>
                    <option value="AutresDepense">Autres</option>
                </optgroup>
            </select>
            <label for="description">Description</label>
            <input type="text" name="description" id="description" placeholder="Description de la transaction">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" required>
            <input type="submit" name="submit" value="Ajouter la transaction">
        </form>
    </div>
</body>
</html>