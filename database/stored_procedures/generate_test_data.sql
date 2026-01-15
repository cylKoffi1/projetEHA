-- =====================================================
-- Procédures stockées pour générer des données de test
-- pour les projets et infrastructures
-- =====================================================

DELIMITER $$

-- =====================================================
-- Procédure principale : génère toutes les données de test
-- =====================================================
DROP PROCEDURE IF EXISTS `generate_all_test_data`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_all_test_data`(
    IN p_pays_code VARCHAR(3),
    IN p_groupe_projet VARCHAR(10),
    IN p_nombre_projets INT,
    IN p_nombre_infrastructures INT
)
BEGIN
    DECLARE v_nombre_relations_acteurs INT;
    DECLARE v_nombre_relations_localites INT;
    DECLARE v_nombre_relations_jouir INT;

    -- Calculer le nombre de relations (environ 2-3 bénéficiaires par projet)
    SET v_nombre_relations_acteurs   = FLOOR(p_nombre_projets * 2.5);
    SET v_nombre_relations_localites = FLOOR(p_nombre_projets * 2.0);
    SET v_nombre_relations_jouir     = FLOOR(p_nombre_projets * 1.5);

    -- 2. Générer les projets
    CALL generate_projets_massifs(p_pays_code, p_groupe_projet, p_nombre_projets);

    -- 3. Générer les relations bénéficiaires-acteurs
    CALL generate_beneficiaires_acteurs(p_pays_code, v_nombre_relations_acteurs);

    -- 4. Générer les relations bénéficiaires-localités
    CALL generate_beneficiaires_localites(p_pays_code, v_nombre_relations_localites);

    -- 5. Générer les infrastructures et relations jouir
    CALL generate_infrastructures_et_jouir(
        p_pays_code, p_groupe_projet, p_nombre_infrastructures, v_nombre_relations_jouir
    );
END$$

-- =====================================================
-- Génère un nombre massif de projets
-- OPTIMISÉ : Suppression des vérifications d'unicité dans la boucle
-- =====================================================
DROP PROCEDURE IF EXISTS `generate_projets_massifs`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_projets_massifs`(
    IN p_pays_code VARCHAR(3),
    IN p_groupe_projet VARCHAR(10),
    IN p_nombre_projets INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_code_projet VARCHAR(100);
    DECLARE v_libelle_projet VARCHAR(500);
    DECLARE v_code_sous_domaine VARCHAR(10);
    DECLARE v_date_debut DATE;
    DECLARE v_date_fin DATE;
    DECLARE v_cout_projet DECIMAL(15,2);
    DECLARE v_code_devise VARCHAR(3);
    DECLARE v_type_financement CHAR(1);
    DECLARE v_annee INT;
    DECLARE v_ordre INT;
    DECLARE v_code_localisation VARCHAR(20);
    DECLARE v_libelle_localisation VARCHAR(20);
    DECLARE v_prefix VARCHAR(100);
    DECLARE v_pays_id INT;
    DECLARE v_localite_count INT;
    DECLARE v_random_offset_localite INT;
    DECLARE v_niveau_max INT;
    DECLARE v_niveau_courant INT;
    DECLARE v_code_nature VARCHAR(10);
    DECLARE v_nature_count INT;
    DECLARE v_random_offset_nature INT;
    DECLARE v_niveau_localite INT;
    DECLARE v_code_decoupage VARCHAR(20);
    DECLARE v_acteur_count INT;
    DECLARE v_random_offset_acteur INT;
    DECLARE v_code_acteur_chef INT;
    DECLARE v_code_acteur_moe INT;
    DECLARE v_code_acteur_moa INT;
    DECLARE v_secteur_id VARCHAR(10);
    DECLARE v_secteur_count INT;
    DECLARE v_random_offset_secteur INT;
    DECLARE v_rand INT;
    
    -- Noms de projets réalistes
    DECLARE projet_prefixes TEXT DEFAULT 
        'Construction,Rehabilitation,Amenagement,Extension,Modernisation,Equipement,Renovation,Developpement,Amelioration,Creation,Installation,Realisation,Optimisation,Valorisation';
    
    DECLARE projet_suffixes TEXT DEFAULT 
        'Route Nationale,Ecole Primaire,Centre de Sante,Hopital,Marche Public,Point d''Eau,Forage,Electricite Rurale,Reseau d''Eau Potable,Pont,Barrage,Irrigation,Ecole Secondaire,Universite,Centre de Formation,Stade,Marche Communal,Infrastructure Sanitaire,Infrastructure Educative,Infrastructure Routiere';
    
    -- Récupérer l'ID du pays une seule fois
    SELECT id INTO v_pays_id
    FROM pays 
    WHERE alpha3 = p_pays_code 
    LIMIT 1;
    
    -- Récupérer une seule fois les valeurs nécessaires (optimisation)
    SELECT COALESCE(code_devise,'XOF') INTO v_code_devise 
    FROM pays 
    WHERE alpha3 = p_pays_code 
    LIMIT 1;
    
    -- Récupérer un code_sous_domaine valide pour ce groupe projet
    SELECT code_sous_domaine INTO v_code_sous_domaine 
    FROM sous_domaine 
    WHERE code_groupe_projet = p_groupe_projet 
    LIMIT 1;
    
    -- Valeurs par défaut si non trouvées
    IF v_code_devise IS NULL THEN 
        SET v_code_devise = 'XOF'; 
    END IF;
    
    IF v_code_sous_domaine IS NULL THEN 
        SET v_code_sous_domaine = '0000'; 
    END IF;
    
    -- Récupérer le niveau administratif maximum (dernier niveau) pour ce pays
    SELECT MAX(lp.id_niveau) INTO v_niveau_max
    FROM localites_pays lp
    WHERE lp.id_pays = p_pays_code;
    
    
    -- Si aucun niveau trouvé, utiliser le niveau 3 par défaut
    IF v_niveau_max IS NULL THEN
        SET v_niveau_max = 3;
        SELECT 'DEBUG PROJETS - Aucun niveau trouvé, utilisation du niveau 3 par défaut' AS debug_info;
    END IF;
    
    -- Trouver le premier niveau qui contient des données (en partant du niveau max)
    niveau_loop: WHILE v_niveau_max >= 1 DO
        SELECT COUNT(*) INTO v_localite_count
        FROM localites_pays 
        WHERE id_pays = p_pays_code
          AND id_niveau = v_niveau_max;
        
        
        IF v_localite_count > 0 THEN
            LEAVE niveau_loop;
        END IF;
        
        SET v_niveau_max = v_niveau_max - 1;
    END WHILE;
    
    -- Si aucun niveau n'a de données, utiliser le niveau 1 par défaut
    IF v_niveau_max < 1 THEN
        SET v_niveau_max = 1;
        SET v_localite_count = 1; -- Éviter division par zéro
        SELECT 'DEBUG PROJETS - Aucun niveau avec données, utilisation du niveau 1 par défaut' AS debug_info;
    END IF;

    -- ==========================================================
    -- Préparation de tables temporaires en mémoire pour accélérer
    -- les sélections aléatoires (remplace OFFSET dans les boucles)
    -- ==========================================================

    -- 1) Localités (niveau max déterminé ci‑dessus)
    DROP TEMPORARY TABLE IF EXISTS tmp_localites;
    CREATE TEMPORARY TABLE tmp_localites (
        num INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code_rattachement VARCHAR(20),
        Libelle VARCHAR(255),
        id_niveau INT,
        code_decoupage VARCHAR(20)
    ) ENGINE=MEMORY;

    INSERT INTO tmp_localites (code_rattachement, Libelle, id_niveau, code_decoupage)
    SELECT code_rattachement, Libelle, id_niveau, code_decoupage
    FROM localites_pays
    WHERE id_pays = p_pays_code
      AND id_niveau = v_niveau_max;

    SELECT COUNT(*) INTO v_localite_count FROM tmp_localites;

    -- 2) Natures de travaux
    DROP TEMPORARY TABLE IF EXISTS tmp_natures;
    CREATE TEMPORARY TABLE tmp_natures (
        num INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(10)
    ) ENGINE=MEMORY;

    INSERT INTO tmp_natures (code)
    SELECT code
    FROM nature_traveaux;

    SELECT COUNT(*) INTO v_nature_count FROM tmp_natures;

    IF v_nature_count > 0 THEN
        -- valeur par défaut : première nature
        SELECT code INTO v_code_nature
        FROM tmp_natures
        WHERE num = 1;
    ELSE
        SET v_code_nature = '001'; -- Code par défaut si la table est vide
    END IF;

    -- 3) Acteurs actifs du pays
    DROP TEMPORARY TABLE IF EXISTS tmp_acteurs;
    CREATE TEMPORARY TABLE tmp_acteurs (
        num INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code_acteur INT
    ) ENGINE=MEMORY;

    INSERT INTO tmp_acteurs (code_acteur)
    SELECT code_acteur
    FROM acteur
    WHERE code_pays = p_pays_code
      AND is_active = 1;

    SELECT COUNT(*) INTO v_acteur_count FROM tmp_acteurs;

    -- 4) Secteurs d'activité
    DROP TEMPORARY TABLE IF EXISTS tmp_secteurs;
    CREATE TEMPORARY TABLE tmp_secteurs (
        num INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(10)
    ) ENGINE=MEMORY;

    INSERT INTO tmp_secteurs (code)
    SELECT code
    FROM secteurs_activite;

    SELECT COUNT(*) INTO v_secteur_count FROM tmp_secteurs;

    IF v_secteur_count > 0 THEN
        SELECT code INTO v_secteur_id
        FROM tmp_secteurs
        WHERE num = 1;
    ELSE
        SET v_secteur_id = '001'; -- Code par défaut si la table est vide
    END IF;

    WHILE i <= p_nombre_projets DO
        -- Générer les composants du code projet
        SET v_type_financement = CASE WHEN (i % 3) = 0 THEN '2' ELSE '1' END;
        
        -- Générer des dates réalistes
        SET v_date_debut = DATE_ADD('2020-01-01', INTERVAL (i % 1800) DAY);
        SET v_date_fin = DATE_ADD(v_date_debut, INTERVAL (180 + (i % 1000)) DAY);
        SET v_annee = YEAR(v_date_debut);
        
        -- Sélection rapide d'une localité aléatoire via la table temporaire
        IF v_localite_count > 0 THEN
            SET v_random_offset_localite = FLOOR(RAND() * v_localite_count) + 1;

            SELECT code_rattachement, Libelle, id_niveau, code_decoupage 
            INTO v_code_localisation, v_libelle_localisation, v_niveau_localite, v_code_decoupage
            FROM tmp_localites
            WHERE num = v_random_offset_localite;
        END IF;
        
        -- Si toujours aucune localité trouvée, utiliser un code par défaut
        IF v_code_localisation IS NULL THEN
            SET v_code_localisation = LPAD((i % 1000000), 6, '0');
            SET v_niveau_localite = 1;
            SET v_code_decoupage = NULL;
            SELECT CONCAT('DEBUG PROJETS [Projet ', i, '] - AUCUNE localité trouvée, utilisation du code par défaut: ', v_code_localisation) AS debug_info;
        END IF;
        
        -- S'assurer que le niveau est défini
        IF v_niveau_localite IS NULL THEN
            SET v_niveau_localite = v_niveau_max;
        END IF;
        
        -- Construire le préfixe du code projet (format: ALPHA3GROUPE{TYPEFIN}_{CODE_RATTACHEMENT}_{SOUSDOMAINE}_{ANNEE})
        SET v_prefix = CONCAT(
            UPPER(p_pays_code),
            UPPER(p_groupe_projet),
            v_type_financement, '_',
            UPPER(v_code_localisation), '_',
            UPPER(v_code_sous_domaine), '_',
            v_annee
        );
        
        -- Générer l'ordre local sans requête COUNT(*) (beaucoup plus rapide)
        SET v_ordre = i;
        
        -- Construire le code projet final (format: ALPHA3GROUPE{TYPEFIN}_{CODE_RATTACHEMENT}_{SOUSDOMAINE}_{ANNEE}_{ORDRE})
        SET v_code_projet = CONCAT(
            v_prefix, '_',
            LPAD(v_ordre, 2, '0')
        );
        
        -- Générer un libellé réaliste
        SET v_libelle_projet = CONCAT(
            SUBSTRING_INDEX(SUBSTRING_INDEX(projet_prefixes, ',', (i % 14) + 1), ',', -1),
            ' ',
            SUBSTRING_INDEX(SUBSTRING_INDEX(projet_suffixes, ',', (i % 20) + 1), ',', -1),
            ' - Zone ', v_libelle_localisation
        );
        
        -- Générer un coût réaliste (entre 10 millions et 30 milliards)
        SET v_cout_projet = 10000000 + (RAND() * 30000000000);
        
        -- Récupérer un code_nature aléatoire pour ce projet (pour varier les natures)
        IF v_nature_count > 0 THEN
            SET v_random_offset_nature = FLOOR(RAND() * v_nature_count) + 1;
            SELECT code INTO v_code_nature
            FROM tmp_natures
            WHERE num = v_random_offset_nature;

            -- Si toujours NULL, utiliser le code par défaut (première nature)
            IF v_code_nature IS NULL THEN
                SELECT code INTO v_code_nature
                FROM tmp_natures
                WHERE num = 1;
            END IF;
        END IF;
        
        -- Utiliser INSERT IGNORE pour éviter les erreurs de doublons (plus rapide que vérifier avant)
        INSERT IGNORE INTO projets (
            code_projet, code_alpha3_pays, libelle_projet,
            code_sous_domaine, date_demarrage_prevue, date_fin_prevue,
            cout_projet, code_devise, created_at, updated_at
        ) VALUES (
            v_code_projet, p_pays_code, v_libelle_projet,
            v_code_sous_domaine, v_date_debut, v_date_fin,
            v_cout_projet, v_code_devise, NOW(), NOW()
        );
        
        -- 1. Créer l'enregistrement dans projets_naturetravaux (pas de timestamps)
        INSERT IGNORE INTO projets_naturetravaux (
            code_projet, code_nature, date
        ) VALUES (
            v_code_projet, v_code_nature, CURDATE()
        );
        
        -- 2. Créer le statut initial du projet (type_statut = 1)
        INSERT IGNORE INTO projet_statut (
            code_projet, type_statut, date_statut, created_at, updated_at
        ) VALUES (
            v_code_projet, 1, NOW(), NOW(), NOW()
        );
        
        -- 3. Créer la localisation du projet
        INSERT IGNORE INTO projetlocalisation (
            code_projet, pays_code, code_localite, niveau, decoupage, created_at, updated_at
        ) VALUES (
            v_code_projet, p_pays_code, v_code_localisation, v_niveau_localite, v_code_decoupage, NOW(), NOW()
        );
        
        -- 4. Créer le chef de projet (Controler) - seulement si des acteurs sont disponibles
        IF v_acteur_count > 0 THEN
            SET v_random_offset_acteur = FLOOR(RAND() * v_acteur_count) + 1;
            SELECT code_acteur INTO v_code_acteur_chef
            FROM tmp_acteurs
            WHERE num = v_random_offset_acteur;
            
            IF v_code_acteur_chef IS NOT NULL THEN
                INSERT IGNORE INTO controler (
                    code_projet, code_acteur, date_debut, date_fin, is_active, created_at, updated_at
                ) VALUES (
                    v_code_projet, v_code_acteur_chef, v_date_debut, v_date_fin, 1, NOW(), NOW()
                );
            END IF;
        END IF;
        
        -- 5. Créer le maître d'ouvrage (Posseder) - seulement si des acteurs sont disponibles
        IF v_acteur_count > 0 THEN
            SET v_random_offset_acteur = FLOOR(RAND() * v_acteur_count) + 1;
            SELECT code_acteur INTO v_code_acteur_moa
            FROM tmp_acteurs
            WHERE num = v_random_offset_acteur;
            
            -- Récupérer un secteur aléatoire pour le MOA
            IF v_secteur_count > 0 THEN
                SET v_random_offset_secteur = FLOOR(RAND() * v_secteur_count) + 1;
                SELECT code INTO v_secteur_id
                FROM tmp_secteurs
                WHERE num = v_random_offset_secteur;
                
                IF v_secteur_id IS NULL THEN
                    SELECT code INTO v_secteur_id
                    FROM tmp_secteurs
                    WHERE num = 1;
                END IF;
            END IF;
            
            IF v_code_acteur_moa IS NOT NULL THEN
                INSERT IGNORE INTO posseder (
                    code_projet, code_acteur, secteur_id, date, isAssistant, is_active, created_at, updated_at
                ) VALUES (
                    v_code_projet, v_code_acteur_moa, v_secteur_id, CURDATE(), 0, 1, NOW(), NOW()
                );
            END IF;
        END IF;
        
        -- 6. Créer le maître d'œuvre (Executer) - seulement si des acteurs sont disponibles
        IF v_acteur_count > 0 THEN
            SET v_random_offset_acteur = FLOOR(RAND() * v_acteur_count) + 1;
            SELECT code_acteur INTO v_code_acteur_moe
            FROM tmp_acteurs
            WHERE num = v_random_offset_acteur;
            
            -- Récupérer un secteur aléatoire pour le MOE
            IF v_secteur_count > 0 THEN
                SET v_random_offset_secteur = FLOOR(RAND() * v_secteur_count) + 1;
                SELECT code INTO v_secteur_id
                FROM tmp_secteurs
                WHERE num = v_random_offset_secteur;
                
                IF v_secteur_id IS NULL THEN
                    SELECT code INTO v_secteur_id
                    FROM tmp_secteurs
                    WHERE num = 1;
                END IF;
            END IF;
            
            IF v_code_acteur_moe IS NOT NULL THEN
                INSERT IGNORE INTO executer (
                    code_projet, code_acteur, secteur_id, is_active, created_at, updated_at
                ) VALUES (
                    v_code_projet, v_code_acteur_moe, v_secteur_id, 1, NOW(), NOW()
                );
            END IF;
        END IF;
        
        SET i = i + 1;
    END WHILE;
END$$

-- =====================================================
-- Génère les relations bénéficiaires-acteurs
-- OPTIMISÉ : Utilisation de tables temporaires et offset aléatoire
-- =====================================================
DROP PROCEDURE IF EXISTS `generate_beneficiaires_acteurs`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_beneficiaires_acteurs`(
    IN p_pays_code VARCHAR(3),
    IN p_nombre_relations INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_code_projet VARCHAR(100);
    DECLARE v_code_acteur INT;
    DECLARE v_projet_count INT;
    DECLARE v_acteur_count INT;
    DECLARE v_random_offset_projet INT;
    DECLARE v_random_offset_acteur INT;

    proc_exit: BEGIN

        -- Compter les projets et acteurs disponibles
        SELECT COUNT(*) INTO v_projet_count
        FROM projets
        WHERE code_alpha3_pays = p_pays_code;

        SELECT COUNT(*) INTO v_acteur_count
        FROM acteur
        WHERE code_pays = p_pays_code
          AND is_active = 1;

        -- S'il n'y a rien à traiter, sortir proprement
        IF v_projet_count = 0 OR v_acteur_count = 0 THEN
            LEAVE proc_exit;
        END IF;

        WHILE i <= p_nombre_relations DO
            -- Utiliser un offset aléatoire au lieu de ORDER BY RAND() (beaucoup plus rapide)
            SET v_random_offset_projet = FLOOR(RAND() * v_projet_count);
            SET v_random_offset_acteur = FLOOR(RAND() * v_acteur_count);

            SELECT code_projet INTO v_code_projet
            FROM projets
            WHERE code_alpha3_pays = p_pays_code
            LIMIT 1 OFFSET v_random_offset_projet;

            SELECT code_acteur INTO v_code_acteur
            FROM acteur
            WHERE code_pays = p_pays_code
              AND is_active = 1
            LIMIT 1 OFFSET v_random_offset_acteur;

            -- Utiliser INSERT IGNORE pour éviter les vérifications NOT EXISTS (plus rapide)
            IF v_code_projet IS NOT NULL AND v_code_acteur IS NOT NULL THEN
                INSERT IGNORE INTO beneficier (
                    code_projet, code_acteur, is_active, created_at, updated_at
                ) VALUES (
                    v_code_projet, v_code_acteur, 1, NOW(), NOW()
                );
            END IF;

            SET i = i + 1;
        END WHILE;

    END proc_exit;
END$$

-- =====================================================
-- Génère les relations bénéficiaires-localités
-- OPTIMISÉ : Utilisation d'offset aléatoire au lieu de ORDER BY RAND()
-- =====================================================
DROP PROCEDURE IF EXISTS `generate_beneficiaires_localites`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_beneficiaires_localites`(
    IN p_pays_code VARCHAR(3),
    IN p_nombre_relations INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_code_projet VARCHAR(100);
    DECLARE v_code_rattachement VARCHAR(20);
    DECLARE v_pays_id INT;
    DECLARE v_projet_count INT;
    DECLARE v_localite_count INT;
    DECLARE v_random_offset_projet INT;
    DECLARE v_random_offset_localite INT;

    proc_exit: BEGIN

        -- Obtenir l'ID du pays
        SELECT id INTO v_pays_id
        FROM pays
        WHERE alpha3 = p_pays_code
        LIMIT 1;

        -- Compter les projets et localités disponibles
        SELECT COUNT(*) INTO v_projet_count
        FROM projets
        WHERE code_alpha3_pays = p_pays_code;

        SELECT COUNT(*) INTO v_localite_count
        FROM localites_pays
        WHERE id_pays = p_pays_code;

        -- S'il n'y a rien à traiter, sortir proprement
        IF v_projet_count = 0 OR v_localite_count = 0 THEN
            LEAVE proc_exit;
        END IF;

        WHILE i <= p_nombre_relations DO
            -- Utiliser un offset aléatoire au lieu de ORDER BY RAND() (beaucoup plus rapide)
            SET v_random_offset_projet = FLOOR(RAND() * v_projet_count);
            SET v_random_offset_localite = FLOOR(RAND() * v_localite_count);

            SELECT code_projet INTO v_code_projet
            FROM projets
            WHERE code_alpha3_pays = p_pays_code
            LIMIT 1 OFFSET v_random_offset_projet;

            SELECT code_rattachement INTO v_code_rattachement
            FROM localites_pays
            WHERE id_pays = p_pays_code
            LIMIT 1 OFFSET v_random_offset_localite;

            -- Utiliser INSERT IGNORE pour éviter les vérifications NOT EXISTS (plus rapide)
            IF v_code_projet IS NOT NULL AND v_code_rattachement IS NOT NULL THEN
                INSERT IGNORE INTO profiter (
                    code_projet, code_pays, code_rattachement, created_at, updated_at
                ) VALUES (
                    v_code_projet, p_pays_code, v_code_rattachement, NOW(), NOW()
                );
            END IF;

            SET i = i + 1;
        END WHILE;

    END proc_exit;
END$$

-- =====================================================
-- Génère les infrastructures et les relations jouir
-- =====================================================
DROP PROCEDURE IF EXISTS `generate_infrastructures_et_jouir`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_infrastructures_et_jouir`(
    IN p_pays_code VARCHAR(3),
    IN p_groupe_projet VARCHAR(10),
    IN p_nombre_infrastructures INT,
    IN p_nombre_relations_jouir INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_code_infra VARCHAR(50);
    DECLARE v_libelle_infra VARCHAR(500);
    DECLARE v_code_projet VARCHAR(100);
    DECLARE v_code_localite VARCHAR(20);
    DECLARE v_libelle_localite VARCHAR(20);
    DECLARE v_pays_id INT;
    DECLARE v_code_Ssys VARCHAR(10);
    DECLARE v_latitude DECIMAL(10,8);
    DECLARE v_longitude DECIMAL(11,8);
    DECLARE v_localite_count INT;
    DECLARE v_random_offset_localite INT;
    DECLARE v_projet_count INT;
    DECLARE v_infra_count INT;
    DECLARE v_random_offset_projet INT;
    DECLARE v_random_offset_infra INT;
    DECLARE v_code_famille VARCHAR(10);
    DECLARE v_infra_prefix VARCHAR(20);
    DECLARE v_infra_ordre INT;
    DECLARE v_niveau_max_infra INT;
    DECLARE v_niveau_courant_infra INT;
    
    -- Types d'infrastructures réalistes
    DECLARE infra_types TEXT DEFAULT 
        'Ecole Primaire,Ecole Secondaire,Universite,Centre de Sante,Hopital,Marche,Route,Pont,Barrage,Forage,Point d''Eau,Reseau Electrique,Centre de Formation,Stade,Marche Communal,Infrastructure Sanitaire';
    
    -- Obtenir l'ID du pays
    SELECT id INTO v_pays_id 
    FROM pays 
    WHERE alpha3 = p_pays_code 
    LIMIT 1;
    
    -- Récupérer un code_Ssys par défaut depuis famille_infrastructure
    SELECT fd.code_Ssys INTO v_code_Ssys 
    FROM familleinfrastructure 
    INNER JOIN famille_domaine fd ON fd.code_Ssys = familleinfrastructure.code_Ssys
    WHERE fd.code_groupe_projet = p_groupe_projet
    LIMIT 1;
    
    IF v_code_Ssys IS NULL THEN
        SELECT code_Ssys INTO v_code_Ssys 
        FROM familleinfrastructure 
        LIMIT 1;
    END IF;
    
    IF v_code_Ssys IS NULL THEN
        SET v_code_Ssys = '001';
    END IF;
    
    -- Récupérer le niveau administratif maximum (dernier niveau) pour ce pays
    SELECT MAX(lp.id_niveau) INTO v_niveau_max_infra
    FROM localites_pays lp
    WHERE lp.id_pays = p_pays_code;
    
    
    -- Si aucun niveau trouvé, utiliser le niveau 3 par défaut
    IF v_niveau_max_infra IS NULL THEN
        SET v_niveau_max_infra = 3;
        SELECT 'DEBUG INFRA - Aucun niveau trouvé, utilisation du niveau 3 par défaut' AS debug_info;
    END IF;
    
    -- Trouver le premier niveau qui contient des données (en partant du niveau max)
    niveau_loop_infra: WHILE v_niveau_max_infra >= 1 DO
        SELECT COUNT(*) INTO v_localite_count
        FROM localites_pays
        WHERE id_pays = p_pays_code
          AND id_niveau = v_niveau_max_infra;
        
        IF v_localite_count > 0 THEN
            LEAVE niveau_loop_infra;
        END IF;
        
        SET v_niveau_max_infra = v_niveau_max_infra - 1;
    END WHILE;
    
    -- Si aucun niveau n'a de données, utiliser le niveau 1 par défaut
    IF v_niveau_max_infra < 1 THEN
        SET v_niveau_max_infra = 1;
        SET v_localite_count = 1; -- Éviter division par zéro
        SELECT 'DEBUG INFRA - Aucun niveau avec données, utilisation du niveau 1 par défaut' AS debug_info;
    END IF;
    
    
    -- Récupérer le code_Ssys (code famille) pour générer les codes infrastructure
    -- Format infrastructure: {ALPHA3}{CODE_FAMILLE}{NUMERO} (ex: CIVHEB000001)
    SET v_code_famille = v_code_Ssys;
    
    -- Construire le préfixe pour les infrastructures
    SET v_infra_prefix = CONCAT(UPPER(p_pays_code), UPPER(v_code_famille));
    
    -- Générer les infrastructures
    WHILE i <= p_nombre_infrastructures DO
        -- Compter les infrastructures existantes avec ce préfixe pour générer l'ordre
        SELECT COUNT(*) + 1 INTO v_infra_ordre
        FROM infrastructures
        WHERE code LIKE CONCAT(v_infra_prefix, '%');
        
        -- Générer le code infrastructure au format: {ALPHA3}{CODE_FAMILLE}{NUMERO} (6 chiffres)
        SET v_code_infra = CONCAT(v_infra_prefix, LPAD(v_infra_ordre, 6, '0'));
        
        -- Utiliser un offset aléatoire au lieu de ORDER BY RAND() (beaucoup plus rapide)
        IF v_localite_count > 0 THEN
            SET v_random_offset_localite = FLOOR(RAND() * v_localite_count);
            
            SELECT code_rattachement, Libelle
            INTO v_code_localite, v_libelle_localite
            FROM localites_pays
            WHERE id_pays = p_pays_code
              AND id_niveau = v_niveau_max_infra
            LIMIT 1 OFFSET v_random_offset_localite;
            
        END IF;
        
        -- Si aucune localité trouvée, chercher dans les niveaux inférieurs jusqu'à trouver des données
        IF v_code_localite IS NULL THEN
            SET v_niveau_courant_infra = v_niveau_max_infra - 1;
            
            recherche_localite_infra: WHILE v_niveau_courant_infra >= 1 DO
                SELECT COUNT(*) INTO v_localite_count
                FROM localites_pays
                WHERE id_pays = p_pays_code
                  AND id_niveau = v_niveau_courant_infra;
                
                
                IF v_localite_count > 0 THEN
                    SET v_random_offset_localite = FLOOR(RAND() * v_localite_count);
                    SELECT code_rattachement, Libelle
                    INTO v_code_localite, v_libelle_localite
                    FROM localites_pays
                    WHERE id_pays = p_pays_code
                      AND id_niveau = v_niveau_courant_infra
                    LIMIT 1 OFFSET v_random_offset_localite;
                    
                    -- Si on a trouvé une localité, on sort de la boucle
                    IF v_code_localite IS NOT NULL THEN
                        LEAVE recherche_localite_infra;
                    END IF;
                END IF;
                
                SET v_niveau_courant_infra = v_niveau_courant_infra - 1;
            END WHILE;
        END IF;
        
        -- Si toujours aucune localité trouvée, utiliser un code par défaut
        IF v_code_localite IS NULL THEN
            SET v_code_localite = LPAD((i % 1000), 6, '0');
            SELECT CONCAT('DEBUG INFRA [Infra ', i, '] - AUCUNE localité trouvée, utilisation du code par défaut: ', v_code_localite) AS debug_info;
        END IF;
        
        -- Générer un libellé réaliste
        SET v_libelle_infra = CONCAT(
            SUBSTRING_INDEX(SUBSTRING_INDEX(infra_types, ',', (i % 16) + 1), ',', -1),
            ' - ', v_libelle_localite
        );
        
        -- Générer des coordonnées GPS réalistes (exemple pour Côte d'Ivoire)
        -- Ajustez ces valeurs selon le pays cible
        SET v_latitude = 5.0 + (RAND() * 5.0);
        SET v_longitude = -8.0 - (RAND() * 2.0);
        
        -- Utiliser INSERT IGNORE pour éviter les vérifications d'unicité (plus rapide)
        INSERT IGNORE INTO infrastructures (
            code, libelle, code_Ssys, code_groupe_projet, code_pays, code_localite,
            date_operation, latitude, longitude, IsOver
        ) VALUES (
            v_code_infra, v_libelle_infra, v_code_Ssys, p_groupe_projet, p_pays_code, v_code_localite,
            DATE_ADD('2020-01-01', INTERVAL (i % 1825) DAY), v_latitude, v_longitude, 0
        );
        
        SET i = i + 1;
    END WHILE;
    
    -- Compter les projets et infrastructures disponibles
    SELECT COUNT(*) INTO v_projet_count
    FROM projets 
    WHERE code_alpha3_pays = p_pays_code;
    
    SELECT COUNT(*) INTO v_infra_count
    FROM infrastructures 
    WHERE code_pays = p_pays_code;
    
    -- Générer les relations jouir (projets -> infrastructures)
    SET i = 1;
    WHILE i <= p_nombre_relations_jouir AND v_projet_count > 0 AND v_infra_count > 0 DO
        -- Utiliser un offset aléatoire au lieu de ORDER BY RAND() (beaucoup plus rapide)
        SET v_random_offset_projet = FLOOR(RAND() * v_projet_count);
        SET v_random_offset_infra = FLOOR(RAND() * v_infra_count);
        
        SELECT code_projet INTO v_code_projet
        FROM projets 
        WHERE code_alpha3_pays = p_pays_code 
        LIMIT 1 OFFSET v_random_offset_projet;
        
        SELECT code INTO v_code_infra
        FROM infrastructures 
        WHERE code_pays = p_pays_code 
        LIMIT 1 OFFSET v_random_offset_infra;
        
        -- Utiliser INSERT IGNORE pour éviter les vérifications NOT EXISTS (plus rapide)
        IF v_code_projet IS NOT NULL AND v_code_infra IS NOT NULL THEN
            INSERT IGNORE INTO jouir (
                code_projet, code_Infrastructure, created_at, updated_at
            ) VALUES (
                v_code_projet, v_code_infra, NOW(), NOW()
            );
        END IF;
        
        SET i = i + 1;
    END WHILE;
END$$

DELIMITER ;