-- ============================================================
-- GamersHUB Local Game Catalog
-- ============================================================
-- Purpose:
--   Seed a local MySQL/MariaDB game catalog for registration/onboarding.
--
-- Design:
--   - The catalog is local and does not depend on Supabase.
--   - External APIs (IGDB/Steam/Epic/etc.) can enrich/search this catalog later.
--   - `source` is a discovery/source classification, NOT an exclusivity claim.
--   - The user's previously discussed games are explicitly included:
--       Valorant, Counter-Strike 2, Minecraft, Grand Theft Auto V,
--       Dota 2, League of Legends, Call of Duty, Euro Truck Simulator 2.
--
-- External game APIs:
--   Steam exposes public store application information through IStoreService.
--   IGDB provides game search/metadata and artwork endpoints.
-- ============================================================

CREATE TABLE IF NOT EXISTS game_catalog (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,

    source ENUM(
        'steam',
        'epic',
        'multi',
        'other'
    ) NOT NULL DEFAULT 'multi',

    external_id VARCHAR(255) NULL,

    cover_url VARCHAR(1000) NULL,
    icon_url VARCHAR(1000) NULL,
    artwork_url VARCHAR(1000) NULL,

    developer VARCHAR(255) NULL,
    publisher VARCHAR(255) NULL,

    release_date DATE NULL,

    description TEXT NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_game_catalog_slug (slug),

    KEY idx_game_catalog_name (name),
    KEY idx_game_catalog_source (source),
    KEY idx_game_catalog_external (source, external_id),
    KEY idx_game_catalog_active_name (is_active, name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed catalog
-- ============================================================

INSERT INTO game_catalog
    (id, name, slug, source)
VALUES
    (1, 'Valorant', 'valorant', 'other'),
    (2, 'Counter-Strike 2', 'counter-strike-2', 'steam'),
    (3, 'Minecraft', 'minecraft', 'other'),
    (4, 'Grand Theft Auto V', 'grand-theft-auto-v', 'multi'),
    (5, 'Dota 2', 'dota-2', 'steam'),
    (6, 'League of Legends', 'league-of-legends', 'other'),
    (7, 'Call of Duty', 'call-of-duty', 'multi'),
    (8, 'Euro Truck Simulator 2', 'euro-truck-simulator-2', 'steam'),
    (9, 'Apex Legends', 'apex-legends', 'multi'),
    (10, 'Fortnite', 'fortnite', 'epic'),
    (11, 'Overwatch 2', 'overwatch-2', 'multi'),
    (12, 'Rocket League', 'rocket-league', 'epic'),
    (13, 'PUBG: BATTLEGROUNDS', 'pubg-battlegrounds', 'multi'),
    (14, 'Rainbow Six Siege', 'rainbow-six-siege', 'multi'),
    (15, 'The Finals', 'the-finals', 'multi'),
    (16, 'Destiny 2', 'destiny-2', 'multi'),
    (17, 'Warframe', 'warframe', 'multi'),
    (18, 'Path of Exile', 'path-of-exile', 'multi'),
    (19, 'Path of Exile 2', 'path-of-exile-2', 'multi'),
    (20, 'Team Fortress 2', 'team-fortress-2', 'steam'),
    (21, 'Garry''s Mod', 'garrys-mod', 'steam'),
    (22, 'Terraria', 'terraria', 'multi'),
    (23, 'Stardew Valley', 'stardew-valley', 'multi'),
    (24, 'Rust', 'rust', 'multi'),
    (25, 'ARK: Survival Evolved', 'ark-survival-evolved', 'multi'),
    (26, 'ARK: Survival Ascended', 'ark-survival-ascended', 'multi'),
    (27, 'DayZ', 'dayz', 'multi'),
    (28, 'Unturned', 'unturned', 'multi'),
    (29, '7 Days to Die', '7-days-to-die', 'multi'),
    (30, 'Don''t Starve Together', 'dont-starve-together', 'multi'),
    (31, 'Palworld', 'palworld', 'multi'),
    (32, 'Enshrouded', 'enshrouded', 'multi'),
    (33, 'Valheim', 'valheim', 'multi'),
    (34, 'Raft', 'raft', 'multi'),
    (35, 'Subnautica', 'subnautica', 'multi'),
    (36, 'Subnautica: Below Zero', 'subnautica-below-zero', 'multi'),
    (37, 'The Forest', 'the-forest', 'multi'),
    (38, 'Sons of the Forest', 'sons-of-the-forest', 'multi'),
    (39, 'Grounded', 'grounded', 'multi'),
    (40, 'Astroneer', 'astroneer', 'multi'),
    (41, 'No Man''s Sky', 'no-mans-sky', 'multi'),
    (42, 'Sea of Thieves', 'sea-of-thieves', 'multi'),
    (43, 'Deep Rock Galactic', 'deep-rock-galactic', 'multi'),
    (44, 'Left 4 Dead 2', 'left-4-dead-2', 'multi'),
    (45, 'Killing Floor 2', 'killing-floor-2', 'multi'),
    (46, 'Payday 2', 'payday-2', 'multi'),
    (47, 'Payday 3', 'payday-3', 'multi'),
    (48, 'Dead by Daylight', 'dead-by-daylight', 'multi'),
    (49, 'Phasmophobia', 'phasmophobia', 'multi'),
    (50, 'Lethal Company', 'lethal-company', 'multi'),
    (51, 'Content Warning', 'content-warning', 'multi'),
    (52, 'Among Us', 'among-us', 'multi'),
    (53, 'Fall Guys', 'fall-guys', 'epic'),
    (54, 'Human: Fall Flat', 'human-fall-flat', 'multi'),
    (55, 'It Takes Two', 'it-takes-two', 'multi'),
    (56, 'A Way Out', 'a-way-out', 'multi'),
    (57, 'Unravel', 'unravel', 'multi'),
    (58, 'Unravel Two', 'unravel-two', 'multi'),
    (59, 'Portal', 'portal', 'multi'),
    (60, 'Portal 2', 'portal-2', 'multi'),
    (61, 'Half-Life', 'half-life', 'multi'),
    (62, 'Half-Life 2', 'half-life-2', 'multi'),
    (63, 'Half-Life 2: Episode One', 'half-life-2-episode-one', 'multi'),
    (64, 'Half-Life 2: Episode Two', 'half-life-2-episode-two', 'multi'),
    (65, 'Half-Life: Alyx', 'half-life-alyx', 'multi'),
    (66, 'Black Mesa', 'black-mesa', 'multi'),
    (67, 'Left 4 Dead', 'left-4-dead', 'multi'),
    (68, 'Counter-Strike', 'counter-strike', 'multi'),
    (69, 'Counter-Strike: Source', 'counter-strike-source', 'multi'),
    (70, 'Counter-Strike: Global Offensive', 'counter-strike-global-offensive', 'multi'),
    (71, 'Dota', 'dota', 'multi'),
    (72, 'Team Fortress Classic', 'team-fortress-classic', 'multi'),
    (73, 'Day of Defeat', 'day-of-defeat', 'multi'),
    (74, 'Day of Defeat: Source', 'day-of-defeat-source', 'multi'),
    (75, 'Age of Empires II: Definitive Edition', 'age-of-empires-ii-definitive-edition', 'multi'),
    (76, 'Age of Empires III: Definitive Edition', 'age-of-empires-iii-definitive-edition', 'multi'),
    (77, 'Age of Empires IV', 'age-of-empires-iv', 'multi'),
    (78, 'Age of Mythology: Retold', 'age-of-mythology-retold', 'multi'),
    (79, 'StarCraft Remastered', 'starcraft-remastered', 'multi'),
    (80, 'StarCraft II', 'starcraft-ii', 'multi'),
    (81, 'Warcraft III: Reforged', 'warcraft-iii-reforged', 'multi'),
    (82, 'Diablo', 'diablo', 'multi'),
    (83, 'Diablo II', 'diablo-ii', 'multi'),
    (84, 'Diablo II: Resurrected', 'diablo-ii-resurrected', 'multi'),
    (85, 'Diablo III', 'diablo-iii', 'multi'),
    (86, 'Diablo IV', 'diablo-iv', 'multi'),
    (87, 'Overwatch', 'overwatch', 'multi'),
    (88, 'Hearthstone', 'hearthstone', 'multi'),
    (89, 'Heroes of the Storm', 'heroes-of-the-storm', 'multi'),
    (90, 'World of Warcraft', 'world-of-warcraft', 'multi'),
    (91, 'World of Warcraft Classic', 'world-of-warcraft-classic', 'multi'),
    (92, 'Guild Wars', 'guild-wars', 'multi'),
    (93, 'Guild Wars 2', 'guild-wars-2', 'multi'),
    (94, 'The Elder Scrolls Online', 'the-elder-scrolls-online', 'multi'),
    (95, 'The Elder Scrolls V: Skyrim', 'the-elder-scrolls-v-skyrim', 'multi'),
    (96, 'The Elder Scrolls IV: Oblivion', 'the-elder-scrolls-iv-oblivion', 'multi'),
    (97, 'The Elder Scrolls III: Morrowind', 'the-elder-scrolls-iii-morrowind', 'multi'),
    (98, 'Fallout', 'fallout', 'multi'),
    (99, 'Fallout 2', 'fallout-2', 'multi'),
    (100, 'Fallout 3', 'fallout-3', 'multi'),
    (101, 'Fallout: New Vegas', 'fallout-new-vegas', 'multi'),
    (102, 'Fallout 4', 'fallout-4', 'multi'),
    (103, 'Fallout 76', 'fallout-76', 'multi'),
    (104, 'Starfield', 'starfield', 'multi'),
    (105, 'The Outer Worlds', 'the-outer-worlds', 'epic'),
    (106, 'The Outer Worlds 2', 'the-outer-worlds-2', 'multi'),
    (107, 'Baldur''s Gate', 'baldurs-gate', 'multi'),
    (108, 'Baldur''s Gate II: Shadows of Amn', 'baldurs-gate-ii-shadows-of-amn', 'multi'),
    (109, 'Baldur''s Gate 3', 'baldurs-gate-3', 'multi'),
    (110, 'Divinity: Original Sin', 'divinity-original-sin', 'multi'),
    (111, 'Divinity: Original Sin 2', 'divinity-original-sin-2', 'multi'),
    (112, 'Dragon Age: Origins', 'dragon-age-origins', 'multi'),
    (113, 'Dragon Age II', 'dragon-age-ii', 'multi'),
    (114, 'Dragon Age: Inquisition', 'dragon-age-inquisition', 'multi'),
    (115, 'Dragon Age: The Veilguard', 'dragon-age-the-veilguard', 'multi'),
    (116, 'Mass Effect', 'mass-effect', 'multi'),
    (117, 'Mass Effect 2', 'mass-effect-2', 'multi'),
    (118, 'Mass Effect 3', 'mass-effect-3', 'multi'),
    (119, 'Mass Effect: Andromeda', 'mass-effect-andromeda', 'multi'),
    (120, 'Star Wars: Knights of the Old Republic', 'star-wars-knights-of-the-old-republic', 'multi'),
    (121, 'Star Wars: Knights of the Old Republic II', 'star-wars-knights-of-the-old-republic-ii', 'multi'),
    (122, 'Star Wars Jedi: Fallen Order', 'star-wars-jedi-fallen-order', 'multi'),
    (123, 'Star Wars Jedi: Survivor', 'star-wars-jedi-survivor', 'multi'),
    (124, 'Star Wars Outlaws', 'star-wars-outlaws', 'multi'),
    (125, 'Star Wars Battlefront', 'star-wars-battlefront', 'multi'),
    (126, 'Star Wars Battlefront II', 'star-wars-battlefront-ii', 'multi'),
    (127, 'Star Wars: The Old Republic', 'star-wars-the-old-republic', 'multi'),
    (128, 'Star Wars: Squadrons', 'star-wars-squadrons', 'multi'),
    (129, 'Red Dead Redemption', 'red-dead-redemption', 'multi'),
    (130, 'Red Dead Redemption 2', 'red-dead-redemption-2', 'multi'),
    (131, 'Grand Theft Auto III', 'grand-theft-auto-iii', 'multi'),
    (132, 'Grand Theft Auto: Vice City', 'grand-theft-auto-vice-city', 'multi'),
    (133, 'Grand Theft Auto: San Andreas', 'grand-theft-auto-san-andreas', 'multi'),
    (134, 'Grand Theft Auto IV', 'grand-theft-auto-iv', 'multi'),
    (135, 'Grand Theft Auto: Episodes from Liberty City', 'grand-theft-auto-episodes-from-liberty-city', 'multi'),
    (136, 'Grand Theft Auto: Vice City Stories', 'grand-theft-auto-vice-city-stories', 'multi'),
    (137, 'Grand Theft Auto: Liberty City Stories', 'grand-theft-auto-liberty-city-stories', 'multi'),
    (138, 'Grand Theft Auto: Chinatown Wars', 'grand-theft-auto-chinatown-wars', 'multi'),
    (139, 'Grand Theft Auto: The Trilogy', 'grand-theft-auto-the-trilogy', 'multi'),
    (140, 'Grand Theft Auto: The Trilogy – The Definitive Edition', 'grand-theft-auto-the-trilogy-the-definitive-edition', 'multi'),
    (141, 'Bully: Scholarship Edition', 'bully-scholarship-edition', 'multi'),
    (142, 'L.A. Noire', 'l-a-noire', 'multi'),
    (143, 'Max Payne', 'max-payne', 'multi'),
    (144, 'Max Payne 2', 'max-payne-2', 'multi'),
    (145, 'Max Payne 3', 'max-payne-3', 'multi'),
    (146, 'Alan Wake', 'alan-wake', 'multi'),
    (147, 'Alan Wake 2', 'alan-wake-2', 'epic'),
    (148, 'Control', 'control', 'epic'),
    (149, 'Quantum Break', 'quantum-break', 'multi'),
    (150, 'Dead Space', 'dead-space', 'multi'),
    (151, 'Dead Space 2', 'dead-space-2', 'multi'),
    (152, 'Dead Space 3', 'dead-space-3', 'multi'),
    (153, 'Dead Space (2023)', 'dead-space-2023', 'multi'),
    (154, 'Resident Evil', 'resident-evil', 'multi'),
    (155, 'Resident Evil 2', 'resident-evil-2', 'multi'),
    (156, 'Resident Evil 3', 'resident-evil-3', 'multi'),
    (157, 'Resident Evil 4', 'resident-evil-4', 'multi'),
    (158, 'Resident Evil 5', 'resident-evil-5', 'multi'),
    (159, 'Resident Evil 6', 'resident-evil-6', 'multi'),
    (160, 'Resident Evil 7: Biohazard', 'resident-evil-7-biohazard', 'multi'),
    (161, 'Resident Evil Village', 'resident-evil-village', 'multi'),
    (162, 'Resident Evil Revelations', 'resident-evil-revelations', 'multi'),
    (163, 'Resident Evil Revelations 2', 'resident-evil-revelations-2', 'multi'),
    (164, 'Resident Evil 0', 'resident-evil-0', 'multi'),
    (165, 'Resident Evil Code: Veronica', 'resident-evil-code-veronica', 'multi'),
    (166, 'Resident Evil 5 Gold Edition', 'resident-evil-5-gold-edition', 'multi'),
    (167, 'Resident Evil 4 (2005)', 'resident-evil-4-2005', 'multi'),
    (168, 'Resident Evil 2 (1998)', 'resident-evil-2-1998', 'multi'),
    (169, 'Resident Evil 3: Nemesis', 'resident-evil-3-nemesis', 'multi'),
    (170, 'Devil May Cry', 'devil-may-cry', 'multi'),
    (171, 'Devil May Cry 2', 'devil-may-cry-2', 'multi'),
    (172, 'Devil May Cry 3: Special Edition', 'devil-may-cry-3-special-edition', 'multi'),
    (173, 'Devil May Cry 4', 'devil-may-cry-4', 'multi'),
    (174, 'Devil May Cry 5', 'devil-may-cry-5', 'multi'),
    (175, 'Dino Crisis', 'dino-crisis', 'multi'),
    (176, 'Onimusha: Warlords', 'onimusha-warlords', 'multi'),
    (177, 'Dragon''s Dogma', 'dragons-dogma', 'multi'),
    (178, 'Dragon''s Dogma: Dark Arisen', 'dragons-dogma-dark-arisen', 'multi'),
    (179, 'Dragon''s Dogma 2', 'dragons-dogma-2', 'multi'),
    (180, 'Monster Hunter: World', 'monster-hunter-world', 'multi'),
    (181, 'Monster Hunter Rise', 'monster-hunter-rise', 'multi'),
    (182, 'Monster Hunter Wilds', 'monster-hunter-wilds', 'multi'),
    (183, 'Monster Hunter: World Iceborne', 'monster-hunter-world-iceborne', 'multi'),
    (184, 'Street Fighter', 'street-fighter', 'multi'),
    (185, 'Street Fighter II', 'street-fighter-ii', 'multi'),
    (186, 'Street Fighter III: 3rd Strike', 'street-fighter-iii-3rd-strike', 'multi'),
    (187, 'Street Fighter IV', 'street-fighter-iv', 'multi'),
    (188, 'Street Fighter V', 'street-fighter-v', 'multi'),
    (189, 'Street Fighter 6', 'street-fighter-6', 'multi'),
    (190, 'Tekken', 'tekken', 'multi'),
    (191, 'Tekken 2', 'tekken-2', 'multi'),
    (192, 'Tekken 3', 'tekken-3', 'multi'),
    (193, 'Tekken 4', 'tekken-4', 'multi'),
    (194, 'Tekken 5', 'tekken-5', 'multi'),
    (195, 'Tekken 6', 'tekken-6', 'multi'),
    (196, 'Tekken 7', 'tekken-7', 'multi'),
    (197, 'Tekken 8', 'tekken-8', 'multi'),
    (198, 'Mortal Kombat', 'mortal-kombat', 'multi'),
    (199, 'Mortal Kombat II', 'mortal-kombat-ii', 'multi'),
    (200, 'Mortal Kombat 3', 'mortal-kombat-3', 'multi'),
    (201, 'Mortal Kombat 4', 'mortal-kombat-4', 'multi'),
    (202, 'Mortal Kombat: Deception', 'mortal-kombat-deception', 'multi'),
    (203, 'Mortal Kombat: Armageddon', 'mortal-kombat-armageddon', 'multi'),
    (204, 'Mortal Kombat X', 'mortal-kombat-x', 'multi'),
    (205, 'Mortal Kombat 11', 'mortal-kombat-11', 'multi'),
    (206, 'Mortal Kombat 1', 'mortal-kombat-1', 'multi'),
    (207, 'Injustice: Gods Among Us', 'injustice-gods-among-us', 'multi'),
    (208, 'Injustice 2', 'injustice-2', 'multi'),
    (209, 'Guilty Gear', 'guilty-gear', 'multi'),
    (210, 'Guilty Gear X', 'guilty-gear-x', 'multi'),
    (211, 'Guilty Gear XX', 'guilty-gear-xx', 'multi'),
    (212, 'Guilty Gear Xrd -SIGN-', 'guilty-gear-xrd-sign', 'multi'),
    (213, 'Guilty Gear -Strive-', 'guilty-gear-strive', 'multi'),
    (214, 'BlazBlue: Calamity Trigger', 'blazblue-calamity-trigger', 'multi'),
    (215, 'BlazBlue: Continuum Shift', 'blazblue-continuum-shift', 'multi'),
    (216, 'BlazBlue: Centralfiction', 'blazblue-centralfiction', 'multi'),
    (217, 'The King of Fighters ''98', 'the-king-of-fighters-98', 'multi'),
    (218, 'The King of Fighters 2002', 'the-king-of-fighters-2002', 'multi'),
    (219, 'The King of Fighters XV', 'the-king-of-fighters-xv', 'multi'),
    (220, 'Soulcalibur', 'soulcalibur', 'multi'),
    (221, 'Soulcalibur II', 'soulcalibur-ii', 'multi'),
    (222, 'Soulcalibur III', 'soulcalibur-iii', 'multi'),
    (223, 'Soulcalibur IV', 'soulcalibur-iv', 'multi'),
    (224, 'Soulcalibur V', 'soulcalibur-v', 'multi'),
    (225, 'Soulcalibur VI', 'soulcalibur-vi', 'multi'),
    (226, 'Dead or Alive 5', 'dead-or-alive-5', 'multi'),
    (227, 'Dead or Alive 6', 'dead-or-alive-6', 'multi'),
    (228, 'Virtua Fighter 5', 'virtua-fighter-5', 'multi'),
    (229, 'Killer Instinct', 'killer-instinct', 'multi'),
    (230, 'Samurai Shodown', 'samurai-shodown', 'multi'),
    (231, 'Granblue Fantasy: Versus', 'granblue-fantasy-versus', 'multi'),
    (232, 'Granblue Fantasy Versus: Rising', 'granblue-fantasy-versus-rising', 'multi'),
    (233, 'Marvel vs. Capcom', 'marvel-vs-capcom', 'multi'),
    (234, 'Marvel vs. Capcom 2', 'marvel-vs-capcom-2', 'multi'),
    (235, 'Marvel vs. Capcom 3', 'marvel-vs-capcom-3', 'multi'),
    (236, 'Marvel vs. Capcom Infinite', 'marvel-vs-capcom-infinite', 'multi'),
    (237, 'Dragon Ball FighterZ', 'dragon-ball-fighterz', 'multi'),
    (238, 'Dragon Ball Xenoverse', 'dragon-ball-xenoverse', 'multi'),
    (239, 'Dragon Ball Xenoverse 2', 'dragon-ball-xenoverse-2', 'multi'),
    (240, 'Dragon Ball Z: Kakarot', 'dragon-ball-z-kakarot', 'multi'),
    (241, 'Dragon Ball: Sparking! ZERO', 'dragon-ball-sparking-zero', 'multi'),
    (242, 'Naruto Shippuden: Ultimate Ninja Storm', 'naruto-shippuden-ultimate-ninja-storm', 'multi'),
    (243, 'Naruto Shippuden: Ultimate Ninja Storm 2', 'naruto-shippuden-ultimate-ninja-storm-2', 'multi'),
    (244, 'Naruto Shippuden: Ultimate Ninja Storm 3', 'naruto-shippuden-ultimate-ninja-storm-3', 'multi'),
    (245, 'Naruto Shippuden: Ultimate Ninja Storm 4', 'naruto-shippuden-ultimate-ninja-storm-4', 'multi'),
    (246, 'One Piece: Pirate Warriors 3', 'one-piece-pirate-warriors-3', 'multi'),
    (247, 'One Piece: Pirate Warriors 4', 'one-piece-pirate-warriors-4', 'multi'),
    (248, 'My Hero One''s Justice', 'my-hero-ones-justice', 'multi'),
    (249, 'My Hero Ultra Rumble', 'my-hero-ultra-rumble', 'multi'),
    (250, 'Demon Slayer -Kimetsu no Yaiba- The Hinokami Chronicles', 'demon-slayer-kimetsu-no-yaiba-the-hinokami-chronicles', 'multi'),
    (251, 'Persona 3 Portable', 'persona-3-portable', 'multi'),
    (252, 'Persona 3 Reload', 'persona-3-reload', 'multi'),
    (253, 'Persona 4 Golden', 'persona-4-golden', 'multi'),
    (254, 'Persona 5', 'persona-5', 'multi'),
    (255, 'Persona 5 Royal', 'persona-5-royal', 'multi'),
    (256, 'Persona 5 Strikers', 'persona-5-strikers', 'multi'),
    (257, 'Shin Megami Tensei V', 'shin-megami-tensei-v', 'multi'),
    (258, 'Shin Megami Tensei V: Vengeance', 'shin-megami-tensei-v-vengeance', 'multi'),
    (259, 'Final Fantasy', 'final-fantasy', 'multi'),
    (260, 'Final Fantasy II', 'final-fantasy-ii', 'multi'),
    (261, 'Final Fantasy III', 'final-fantasy-iii', 'multi'),
    (262, 'Final Fantasy IV', 'final-fantasy-iv', 'multi'),
    (263, 'Final Fantasy V', 'final-fantasy-v', 'multi'),
    (264, 'Final Fantasy VI', 'final-fantasy-vi', 'multi'),
    (265, 'Final Fantasy VII', 'final-fantasy-vii', 'multi'),
    (266, 'Final Fantasy VII Remake', 'final-fantasy-vii-remake', 'multi'),
    (267, 'Final Fantasy VII Rebirth', 'final-fantasy-vii-rebirth', 'multi'),
    (268, 'Final Fantasy VIII', 'final-fantasy-viii', 'multi'),
    (269, 'Final Fantasy IX', 'final-fantasy-ix', 'multi'),
    (270, 'Final Fantasy X', 'final-fantasy-x', 'multi'),
    (271, 'Final Fantasy X-2', 'final-fantasy-x-2', 'multi'),
    (272, 'Final Fantasy XI', 'final-fantasy-xi', 'multi'),
    (273, 'Final Fantasy XII', 'final-fantasy-xii', 'multi'),
    (274, 'Final Fantasy XIII', 'final-fantasy-xiii', 'multi'),
    (275, 'Final Fantasy XIII-2', 'final-fantasy-xiii-2', 'multi'),
    (276, 'Lightning Returns: Final Fantasy XIII', 'lightning-returns-final-fantasy-xiii', 'multi'),
    (277, 'Final Fantasy XIV Online', 'final-fantasy-xiv-online', 'multi'),
    (278, 'Final Fantasy XV', 'final-fantasy-xv', 'multi'),
    (279, 'Final Fantasy XVI', 'final-fantasy-xvi', 'multi'),
    (280, 'Final Fantasy Type-0 HD', 'final-fantasy-type-0-hd', 'multi'),
    (281, 'Final Fantasy Tactics', 'final-fantasy-tactics', 'multi'),
    (282, 'Crisis Core: Final Fantasy VII Reunion', 'crisis-core-final-fantasy-vii-reunion', 'multi'),
    (283, 'Chrono Trigger', 'chrono-trigger', 'multi'),
    (284, 'Chrono Cross: The Radical Dreamers Edition', 'chrono-cross-the-radical-dreamers-edition', 'multi'),
    (285, 'NieR Replicant', 'nier-replicant', 'multi'),
    (286, 'NieR:Automata', 'nier-automata', 'multi'),
    (287, 'Kingdom Hearts', 'kingdom-hearts', 'multi'),
    (288, 'Kingdom Hearts II', 'kingdom-hearts-ii', 'multi'),
    (289, 'Kingdom Hearts III', 'kingdom-hearts-iii', 'epic'),
    (290, 'Octopath Traveler', 'octopath-traveler', 'multi'),
    (291, 'Octopath Traveler II', 'octopath-traveler-ii', 'multi'),
    (292, 'Bravely Default', 'bravely-default', 'multi'),
    (293, 'Bravely Default II', 'bravely-default-ii', 'multi'),
    (294, 'Triangle Strategy', 'triangle-strategy', 'multi'),
    (295, 'Tactics Ogre: Reborn', 'tactics-ogre-reborn', 'multi'),
    (296, 'The DioField Chronicle', 'the-diofield-chronicle', 'multi'),
    (297, 'Dragon Quest XI: Echoes of an Elusive Age', 'dragon-quest-xi-echoes-of-an-elusive-age', 'multi'),
    (298, 'Dragon Quest Builders', 'dragon-quest-builders', 'multi'),
    (299, 'Dragon Quest Builders 2', 'dragon-quest-builders-2', 'multi'),
    (300, 'Dragon Quest Monsters: The Dark Prince', 'dragon-quest-monsters-the-dark-prince', 'multi'),
    (301, 'Yakuza 0', 'yakuza-0', 'multi'),
    (302, 'Yakuza Kiwami', 'yakuza-kiwami', 'multi'),
    (303, 'Yakuza Kiwami 2', 'yakuza-kiwami-2', 'multi'),
    (304, 'Yakuza 3 Remastered', 'yakuza-3-remastered', 'multi'),
    (305, 'Yakuza 4 Remastered', 'yakuza-4-remastered', 'multi'),
    (306, 'Yakuza 5 Remastered', 'yakuza-5-remastered', 'multi'),
    (307, 'Yakuza 6: The Song of Life', 'yakuza-6-the-song-of-life', 'multi'),
    (308, 'Yakuza: Like a Dragon', 'yakuza-like-a-dragon', 'multi'),
    (309, 'Like a Dragon: Infinite Wealth', 'like-a-dragon-infinite-wealth', 'multi'),
    (310, 'Like a Dragon Gaiden: The Man Who Erased His Name', 'like-a-dragon-gaiden-the-man-who-erased-his-name', 'multi'),
    (311, 'Judgment', 'judgment', 'multi'),
    (312, 'Lost Judgment', 'lost-judgment', 'multi'),
    (313, 'Shin Megami Tensei III Nocturne HD Remaster', 'shin-megami-tensei-iii-nocturne-hd-remaster', 'multi'),
    (314, 'Tales of Arise', 'tales-of-arise', 'multi'),
    (315, 'Tales of Berseria', 'tales-of-berseria', 'multi'),
    (316, 'Tales of Vesperia: Definitive Edition', 'tales-of-vesperia-definitive-edition', 'multi'),
    (317, 'Tales of Symphonia', 'tales-of-symphonia', 'multi'),
    (318, 'Tales of Zestiria', 'tales-of-zestiria', 'multi'),
    (319, 'Ys VIII: Lacrimosa of Dana', 'ys-viii-lacrimosa-of-dana', 'multi'),
    (320, 'Ys IX: Monstrum Nox', 'ys-ix-monstrum-nox', 'multi'),
    (321, 'Ys X: Nordics', 'ys-x-nordics', 'multi'),
    (322, 'Trails in the Sky', 'trails-in-the-sky', 'multi'),
    (323, 'Trails of Cold Steel', 'trails-of-cold-steel', 'multi'),
    (324, 'Trails of Cold Steel II', 'trails-of-cold-steel-ii', 'multi'),
    (325, 'Trails of Cold Steel III', 'trails-of-cold-steel-iii', 'multi'),
    (326, 'Trails of Cold Steel IV', 'trails-of-cold-steel-iv', 'multi'),
    (327, 'The Legend of Heroes: Trails into Reverie', 'the-legend-of-heroes-trails-into-reverie', 'multi'),
    (328, 'Metaphor: ReFantazio', 'metaphor-refantazio', 'multi'),
    (329, 'Elden Ring', 'elden-ring', 'multi'),
    (330, 'Elden Ring: Shadow of the Erdtree', 'elden-ring-shadow-of-the-erdtree', 'multi'),
    (331, 'Dark Souls', 'dark-souls', 'multi'),
    (332, 'Dark Souls II', 'dark-souls-ii', 'multi'),
    (333, 'Dark Souls III', 'dark-souls-iii', 'multi'),
    (334, 'Demon''s Souls', 'demons-souls', 'multi'),
    (335, 'Bloodborne', 'bloodborne', 'multi'),
    (336, 'Sekiro: Shadows Die Twice', 'sekiro-shadows-die-twice', 'multi'),
    (337, 'Armored Core VI: Fires of Rubicon', 'armored-core-vi-fires-of-rubicon', 'multi'),
    (338, 'Lies of P', 'lies-of-p', 'multi'),
    (339, 'Lords of the Fallen', 'lords-of-the-fallen', 'multi'),
    (340, 'Lords of the Fallen (2014)', 'lords-of-the-fallen-2014', 'multi'),
    (341, 'Nioh', 'nioh', 'multi'),
    (342, 'Nioh 2', 'nioh-2', 'multi'),
    (343, 'Wo Long: Fallen Dynasty', 'wo-long-fallen-dynasty', 'multi'),
    (344, 'Rise of the Ronin', 'rise-of-the-ronin', 'multi'),
    (345, 'Code Vein', 'code-vein', 'multi'),
    (346, 'Remnant: From the Ashes', 'remnant-from-the-ashes', 'multi'),
    (347, 'Remnant II', 'remnant-ii', 'multi'),
    (348, 'Mortal Shell', 'mortal-shell', 'multi'),
    (349, 'The Surge', 'the-surge', 'multi'),
    (350, 'The Surge 2', 'the-surge-2', 'multi'),
    (351, 'Ashen', 'ashen', 'multi'),
    (352, 'Salt and Sanctuary', 'salt-and-sanctuary', 'multi'),
    (353, 'Salt and Sacrifice', 'salt-and-sacrifice', 'multi'),
    (354, 'Hades', 'hades', 'multi'),
    (355, 'Hades II', 'hades-ii', 'multi'),
    (356, 'Dead Cells', 'dead-cells', 'multi'),
    (357, 'Hollow Knight', 'hollow-knight', 'multi'),
    (358, 'Hollow Knight: Silksong', 'hollow-knight-silksong', 'multi'),
    (359, 'Cuphead', 'cuphead', 'multi'),
    (360, 'Ori and the Blind Forest', 'ori-and-the-blind-forest', 'multi'),
    (361, 'Ori and the Will of the Wisps', 'ori-and-the-will-of-the-wisps', 'multi'),
    (362, 'Celeste', 'celeste', 'multi'),
    (363, 'Shovel Knight: Treasure Trove', 'shovel-knight-treasure-trove', 'multi'),
    (364, 'The Messenger', 'the-messenger', 'multi'),
    (365, 'Blasphemous', 'blasphemous', 'multi'),
    (366, 'Blasphemous 2', 'blasphemous-2', 'multi'),
    (367, 'Axiom Verge', 'axiom-verge', 'multi'),
    (368, 'Axiom Verge 2', 'axiom-verge-2', 'multi'),
    (369, 'Hyper Light Drifter', 'hyper-light-drifter', 'multi'),
    (370, 'Hyper Light Breaker', 'hyper-light-breaker', 'multi'),
    (371, 'Katana ZERO', 'katana-zero', 'multi'),
    (372, 'Hotline Miami', 'hotline-miami', 'multi'),
    (373, 'Hotline Miami 2: Wrong Number', 'hotline-miami-2-wrong-number', 'multi'),
    (374, 'Enter the Gungeon', 'enter-the-gungeon', 'multi'),
    (375, 'Exit the Gungeon', 'exit-the-gungeon', 'multi'),
    (376, 'The Binding of Isaac: Rebirth', 'the-binding-of-isaac-rebirth', 'multi'),
    (377, 'The Binding of Isaac', 'the-binding-of-isaac', 'multi'),
    (378, 'Risk of Rain', 'risk-of-rain', 'multi'),
    (379, 'Risk of Rain 2', 'risk-of-rain-2', 'multi'),
    (380, 'Risk of Rain Returns', 'risk-of-rain-returns', 'multi'),
    (381, 'Rogue Legacy', 'rogue-legacy', 'multi'),
    (382, 'Rogue Legacy 2', 'rogue-legacy-2', 'multi'),
    (383, 'Vampire Survivors', 'vampire-survivors', 'multi'),
    (384, 'Brotato', 'brotato', 'multi'),
    (385, 'Halls of Torment', 'halls-of-torment', 'multi'),
    (386, 'Balatro', 'balatro', 'multi'),
    (387, 'Slay the Spire', 'slay-the-spire', 'multi'),
    (388, 'Inscryption', 'inscryption', 'multi'),
    (389, 'Loop Hero', 'loop-hero', 'multi'),
    (390, 'Darkest Dungeon', 'darkest-dungeon', 'multi'),
    (391, 'Darkest Dungeon II', 'darkest-dungeon-ii', 'multi'),
    (392, 'FTL: Faster Than Light', 'ftl-faster-than-light', 'multi'),
    (393, 'Into the Breach', 'into-the-breach', 'multi'),
    (394, 'Papers, Please', 'papers-please', 'multi'),
    (395, 'Return of the Obra Dinn', 'return-of-the-obra-dinn', 'multi'),
    (396, 'Undertale', 'undertale', 'multi'),
    (397, 'Deltarune', 'deltarune', 'multi'),
    (398, 'Disco Elysium', 'disco-elysium', 'multi'),
    (399, 'Kentucky Route Zero', 'kentucky-route-zero', 'multi'),
    (400, 'Outer Wilds', 'outer-wilds', 'multi'),
    (401, 'The Stanley Parable', 'the-stanley-parable', 'multi'),
    (402, 'The Stanley Parable: Ultra Deluxe', 'the-stanley-parable-ultra-deluxe', 'multi'),
    (403, 'What Remains of Edith Finch', 'what-remains-of-edith-finch', 'multi'),
    (404, 'Firewatch', 'firewatch', 'multi'),
    (405, 'Gone Home', 'gone-home', 'multi'),
    (406, 'Oxenfree', 'oxenfree', 'multi'),
    (407, 'Oxenfree II: Lost Signals', 'oxenfree-ii-lost-signals', 'multi'),
    (408, 'Night in the Woods', 'night-in-the-woods', 'multi'),
    (409, 'Life Is Strange', 'life-is-strange', 'multi'),
    (410, 'Life Is Strange: Before the Storm', 'life-is-strange-before-the-storm', 'multi'),
    (411, 'Life Is Strange 2', 'life-is-strange-2', 'multi'),
    (412, 'Life Is Strange: True Colors', 'life-is-strange-true-colors', 'multi'),
    (413, 'Tell Me Why', 'tell-me-why', 'multi'),
    (414, 'Twin Mirror', 'twin-mirror', 'multi'),
    (415, 'Road 96', 'road-96', 'multi'),
    (416, 'Detroit: Become Human', 'detroit-become-human', 'multi'),
    (417, 'Heavy Rain', 'heavy-rain', 'multi'),
    (418, 'Beyond: Two Souls', 'beyond-two-souls', 'multi'),
    (419, 'Until Dawn', 'until-dawn', 'multi'),
    (420, 'The Quarry', 'the-quarry', 'multi'),
    (421, 'Telltale''s The Walking Dead', 'telltales-the-walking-dead', 'multi'),
    (422, 'The Walking Dead: Season Two', 'the-walking-dead-season-two', 'multi'),
    (423, 'The Walking Dead: A New Frontier', 'the-walking-dead-a-new-frontier', 'multi'),
    (424, 'The Walking Dead: The Final Season', 'the-walking-dead-the-final-season', 'multi'),
    (425, 'The Wolf Among Us', 'the-wolf-among-us', 'multi'),
    (426, 'Tales from the Borderlands', 'tales-from-the-borderlands', 'multi'),
    (427, 'Batman: The Telltale Series', 'batman-the-telltale-series', 'multi'),
    (428, 'Minecraft: Story Mode', 'minecraft-story-mode', 'multi'),
    (429, 'Minecraft: Story Mode – Season Two', 'minecraft-story-mode-season-two', 'multi'),
    (430, 'Borderlands', 'borderlands', 'multi'),
    (431, 'Borderlands 2', 'borderlands-2', 'multi'),
    (432, 'Borderlands: The Pre-Sequel', 'borderlands-the-pre-sequel', 'multi'),
    (433, 'Borderlands 3', 'borderlands-3', 'multi'),
    (434, 'Tiny Tina''s Wonderlands', 'tiny-tinas-wonderlands', 'multi'),
    (435, 'Tiny Tina''s Assault on Dragon Keep', 'tiny-tinas-assault-on-dragon-keep', 'multi'),
    (436, 'BioShock', 'bioshock', 'multi'),
    (437, 'BioShock 2', 'bioshock-2', 'multi'),
    (438, 'BioShock Infinite', 'bioshock-infinite', 'multi'),
    (439, 'System Shock', 'system-shock', 'multi'),
    (440, 'System Shock 2', 'system-shock-2', 'multi'),
    (441, 'Prey', 'prey', 'multi'),
    (442, 'Dishonored', 'dishonored', 'multi'),
    (443, 'Dishonored 2', 'dishonored-2', 'multi'),
    (444, 'Dishonored: Death of the Outsider', 'dishonored-death-of-the-outsider', 'multi'),
    (445, 'Deathloop', 'deathloop', 'multi'),
    (446, 'Doom', 'doom', 'multi'),
    (447, 'Doom II', 'doom-ii', 'multi'),
    (448, 'Doom 3', 'doom-3', 'multi'),
    (449, 'Doom (2016)', 'doom-2016', 'multi'),
    (450, 'Doom Eternal', 'doom-eternal', 'multi'),
    (451, 'Quake', 'quake', 'multi'),
    (452, 'Quake II', 'quake-ii', 'multi'),
    (453, 'Quake III Arena', 'quake-iii-arena', 'multi'),
    (454, 'Quake 4', 'quake-4', 'multi'),
    (455, 'Wolfenstein: The New Order', 'wolfenstein-the-new-order', 'multi'),
    (456, 'Wolfenstein: The Old Blood', 'wolfenstein-the-old-blood', 'multi'),
    (457, 'Wolfenstein II: The New Colossus', 'wolfenstein-ii-the-new-colossus', 'multi'),
    (458, 'Wolfenstein: Youngblood', 'wolfenstein-youngblood', 'multi'),
    (459, 'Rage', 'rage', 'multi'),
    (460, 'Rage 2', 'rage-2', 'multi'),
    (461, 'Crysis', 'crysis', 'multi'),
    (462, 'Crysis 2', 'crysis-2', 'multi'),
    (463, 'Crysis 3', 'crysis-3', 'multi'),
    (464, 'Crysis Remastered', 'crysis-remastered', 'multi'),
    (465, 'Crysis 2 Remastered', 'crysis-2-remastered', 'multi'),
    (466, 'Crysis 3 Remastered', 'crysis-3-remastered', 'multi'),
    (467, 'Far Cry', 'far-cry', 'multi'),
    (468, 'Far Cry 2', 'far-cry-2', 'multi'),
    (469, 'Far Cry 3', 'far-cry-3', 'multi'),
    (470, 'Far Cry 3: Blood Dragon', 'far-cry-3-blood-dragon', 'multi'),
    (471, 'Far Cry 4', 'far-cry-4', 'multi'),
    (472, 'Far Cry 5', 'far-cry-5', 'multi'),
    (473, 'Far Cry 6', 'far-cry-6', 'multi'),
    (474, 'Far Cry New Dawn', 'far-cry-new-dawn', 'multi'),
    (475, 'Assassin''s Creed', 'assassins-creed', 'multi'),
    (476, 'Assassin''s Creed II', 'assassins-creed-ii', 'multi'),
    (477, 'Assassin''s Creed Brotherhood', 'assassins-creed-brotherhood', 'multi'),
    (478, 'Assassin''s Creed Revelations', 'assassins-creed-revelations', 'multi'),
    (479, 'Assassin''s Creed III', 'assassins-creed-iii', 'multi'),
    (480, 'Assassin''s Creed IV: Black Flag', 'assassins-creed-iv-black-flag', 'multi'),
    (481, 'Assassin''s Creed Rogue', 'assassins-creed-rogue', 'multi'),
    (482, 'Assassin''s Creed Unity', 'assassins-creed-unity', 'multi'),
    (483, 'Assassin''s Creed Syndicate', 'assassins-creed-syndicate', 'multi'),
    (484, 'Assassin''s Creed Origins', 'assassins-creed-origins', 'multi'),
    (485, 'Assassin''s Creed Odyssey', 'assassins-creed-odyssey', 'multi'),
    (486, 'Assassin''s Creed Valhalla', 'assassins-creed-valhalla', 'multi'),
    (487, 'Assassin''s Creed Mirage', 'assassins-creed-mirage', 'multi'),
    (488, 'Prince of Persia: The Sands of Time', 'prince-of-persia-the-sands-of-time', 'multi'),
    (489, 'Prince of Persia: Warrior Within', 'prince-of-persia-warrior-within', 'multi'),
    (490, 'Prince of Persia: The Two Thrones', 'prince-of-persia-the-two-thrones', 'multi'),
    (491, 'Prince of Persia', 'prince-of-persia', 'multi'),
    (492, 'Prince of Persia: The Forgotten Sands', 'prince-of-persia-the-forgotten-sands', 'multi'),
    (493, 'Watch Dogs', 'watch-dogs', 'multi'),
    (494, 'Watch Dogs 2', 'watch-dogs-2', 'multi'),
    (495, 'Watch Dogs: Legion', 'watch-dogs-legion', 'multi'),
    (496, 'Tom Clancy''s Splinter Cell', 'tom-clancys-splinter-cell', 'multi'),
    (497, 'Tom Clancy''s Splinter Cell: Chaos Theory', 'tom-clancys-splinter-cell-chaos-theory', 'multi'),
    (498, 'Tom Clancy''s Splinter Cell: Double Agent', 'tom-clancys-splinter-cell-double-agent', 'multi'),
    (499, 'Tom Clancy''s Splinter Cell: Conviction', 'tom-clancys-splinter-cell-conviction', 'multi'),
    (500, 'Tom Clancy''s Splinter Cell: Blacklist', 'tom-clancys-splinter-cell-blacklist', 'multi'),
    (501, 'Tom Clancy''s Ghost Recon', 'tom-clancys-ghost-recon', 'multi'),
    (502, 'Tom Clancy''s Ghost Recon Advanced Warfighter', 'tom-clancys-ghost-recon-advanced-warfighter', 'multi'),
    (503, 'Tom Clancy''s Ghost Recon Advanced Warfighter 2', 'tom-clancys-ghost-recon-advanced-warfighter-2', 'multi'),
    (504, 'Tom Clancy''s Ghost Recon Future Soldier', 'tom-clancys-ghost-recon-future-soldier', 'multi'),
    (505, 'Tom Clancy''s Ghost Recon Wildlands', 'tom-clancys-ghost-recon-wildlands', 'multi'),
    (506, 'Tom Clancy''s Ghost Recon Breakpoint', 'tom-clancys-ghost-recon-breakpoint', 'multi'),
    (507, 'Tom Clancy''s Rainbow Six', 'tom-clancys-rainbow-six', 'multi'),
    (508, 'Tom Clancy''s Rainbow Six 3: Raven Shield', 'tom-clancys-rainbow-six-3-raven-shield', 'multi'),
    (509, 'Tom Clancy''s Rainbow Six Vegas', 'tom-clancys-rainbow-six-vegas', 'multi'),
    (510, 'Tom Clancy''s Rainbow Six Vegas 2', 'tom-clancys-rainbow-six-vegas-2', 'multi'),
    (511, 'Tom Clancy''s Rainbow Six Siege', 'tom-clancys-rainbow-six-siege', 'multi'),
    (512, 'Tom Clancy''s Rainbow Six Extraction', 'tom-clancys-rainbow-six-extraction', 'multi'),
    (513, 'Tom Clancy''s The Division', 'tom-clancys-the-division', 'multi'),
    (514, 'Tom Clancy''s The Division 2', 'tom-clancys-the-division-2', 'multi'),
    (515, 'Just Dance', 'just-dance', 'multi'),
    (516, 'The Crew', 'the-crew', 'multi'),
    (517, 'The Crew 2', 'the-crew-2', 'multi'),
    (518, 'The Crew Motorfest', 'the-crew-motorfest', 'multi'),
    (519, 'Trackmania', 'trackmania', 'multi'),
    (520, 'Trials Evolution Gold Edition', 'trials-evolution-gold-edition', 'multi'),
    (521, 'Trials Fusion', 'trials-fusion', 'multi'),
    (522, 'Trials Rising', 'trials-rising', 'multi'),
    (523, 'Rayman Origins', 'rayman-origins', 'multi'),
    (524, 'Rayman Legends', 'rayman-legends', 'multi'),
    (525, 'Prince of Persia: The Lost Crown', 'prince-of-persia-the-lost-crown', 'multi'),
    (526, 'Far Cry Primal', 'far-cry-primal', 'multi'),
    (527, 'Forza Horizon', 'forza-horizon', 'multi'),
    (528, 'Forza Horizon 2', 'forza-horizon-2', 'multi'),
    (529, 'Forza Horizon 3', 'forza-horizon-3', 'multi'),
    (530, 'Forza Horizon 4', 'forza-horizon-4', 'multi'),
    (531, 'Forza Horizon 5', 'forza-horizon-5', 'multi'),
    (532, 'Forza Motorsport', 'forza-motorsport', 'multi'),
    (533, 'Forza Motorsport 7', 'forza-motorsport-7', 'multi'),
    (534, 'Halo: Combat Evolved', 'halo-combat-evolved', 'multi'),
    (535, 'Halo 2', 'halo-2', 'multi'),
    (536, 'Halo 3', 'halo-3', 'multi'),
    (537, 'Halo 3: ODST', 'halo-3-odst', 'multi'),
    (538, 'Halo: Reach', 'halo-reach', 'multi'),
    (539, 'Halo 4', 'halo-4', 'multi'),
    (540, 'Halo 5: Guardians', 'halo-5-guardians', 'multi'),
    (541, 'Halo Infinite', 'halo-infinite', 'multi'),
    (542, 'Halo Wars', 'halo-wars', 'multi'),
    (543, 'Halo Wars 2', 'halo-wars-2', 'multi'),
    (544, 'Gears of War', 'gears-of-war', 'multi'),
    (545, 'Gears of War 2', 'gears-of-war-2', 'multi'),
    (546, 'Gears of War 3', 'gears-of-war-3', 'multi'),
    (547, 'Gears of War 4', 'gears-of-war-4', 'multi'),
    (548, 'Gears 5', 'gears-5', 'multi'),
    (549, 'Gears of War: Judgment', 'gears-of-war-judgment', 'multi'),
    (550, 'Microsoft Flight Simulator', 'microsoft-flight-simulator', 'multi'),
    (551, 'Microsoft Flight Simulator X', 'microsoft-flight-simulator-x', 'multi'),
    (552, 'Age of Empires Online', 'age-of-empires-online', 'multi'),
    (553, 'State of Decay', 'state-of-decay', 'multi'),
    (554, 'State of Decay 2', 'state-of-decay-2', 'multi'),
    (555, 'Bleeding Edge', 'bleeding-edge', 'multi'),
    (556, 'Hi-Fi Rush', 'hi-fi-rush', 'multi'),
    (557, 'Pentiment', 'pentiment', 'multi'),
    (558, 'Psychonauts', 'psychonauts', 'multi'),
    (559, 'Psychonauts 2', 'psychonauts-2', 'multi'),
    (560, 'Sunset Overdrive', 'sunset-overdrive', 'multi'),
    (561, 'Kinect Sports', 'kinect-sports', 'multi'),
    (562, 'Ori and the Blind Forest: Definitive Edition', 'ori-and-the-blind-forest-definitive-edition', 'multi'),
    (563, 'Gears Tactics', 'gears-tactics', 'multi'),
    (564, 'Minecraft Dungeons', 'minecraft-dungeons', 'multi'),
    (565, 'Minecraft Legends', 'minecraft-legends', 'multi'),
    (566, 'Microsoft Solitaire Collection', 'microsoft-solitaire-collection', 'multi'),
    (567, 'Wasteland', 'wasteland', 'multi'),
    (568, 'Wasteland 2', 'wasteland-2', 'multi'),
    (569, 'Wasteland 3', 'wasteland-3', 'multi'),
    (570, 'Pillars of Eternity', 'pillars-of-eternity', 'multi'),
    (571, 'Pillars of Eternity II: Deadfire', 'pillars-of-eternity-ii-deadfire', 'multi'),
    (572, 'Tyranny', 'tyranny', 'multi'),
    (573, 'Pathfinder: Kingmaker', 'pathfinder-kingmaker', 'multi'),
    (574, 'Pathfinder: Wrath of the Righteous', 'pathfinder-wrath-of-the-righteous', 'multi'),
    (575, 'Warhammer 40,000: Rogue Trader', 'warhammer-40-000-rogue-trader', 'multi'),
    (576, 'Warhammer 40,000: Space Marine', 'warhammer-40-000-space-marine', 'multi'),
    (577, 'Warhammer 40,000: Space Marine 2', 'warhammer-40-000-space-marine-2', 'multi'),
    (578, 'Warhammer 40,000: Darktide', 'warhammer-40-000-darktide', 'multi'),
    (579, 'Warhammer: Vermintide', 'warhammer-vermintide', 'multi'),
    (580, 'Warhammer: Vermintide 2', 'warhammer-vermintide-2', 'multi'),
    (581, 'Total War: Warhammer', 'total-war-warhammer', 'multi'),
    (582, 'Total War: Warhammer II', 'total-war-warhammer-ii', 'multi'),
    (583, 'Total War: Warhammer III', 'total-war-warhammer-iii', 'multi'),
    (584, 'Total War: Three Kingdoms', 'total-war-three-kingdoms', 'multi'),
    (585, 'Total War: Rome II', 'total-war-rome-ii', 'multi'),
    (586, 'Total War: Rome', 'total-war-rome', 'multi'),
    (587, 'Total War: Shogun 2', 'total-war-shogun-2', 'multi'),
    (588, 'Total War: Attila', 'total-war-attila', 'multi'),
    (589, 'Total War: Medieval II', 'total-war-medieval-ii', 'multi'),
    (590, 'Total War: Napoleon', 'total-war-napoleon', 'multi'),
    (591, 'Total War: Empire', 'total-war-empire', 'multi'),
    (592, 'Civilization', 'civilization', 'multi'),
    (593, 'Civilization II', 'civilization-ii', 'multi'),
    (594, 'Civilization III', 'civilization-iii', 'multi'),
    (595, 'Civilization IV', 'civilization-iv', 'multi'),
    (596, 'Civilization V', 'civilization-v', 'multi'),
    (597, 'Civilization VI', 'civilization-vi', 'multi'),
    (598, 'Sid Meier''s Civilization VII', 'sid-meiers-civilization-vii', 'multi'),
    (599, 'Sid Meier''s Alpha Centauri', 'sid-meiers-alpha-centauri', 'multi'),
    (600, 'XCOM: Enemy Unknown', 'xcom-enemy-unknown', 'multi'),
    (601, 'XCOM 2', 'xcom-2', 'multi'),
    (602, 'XCOM: Chimera Squad', 'xcom-chimera-squad', 'multi'),
    (603, 'The Sims', 'the-sims', 'multi'),
    (604, 'The Sims 2', 'the-sims-2', 'multi'),
    (605, 'The Sims 3', 'the-sims-3', 'multi'),
    (606, 'The Sims 4', 'the-sims-4', 'multi'),
    (607, 'SimCity', 'simcity', 'multi'),
    (608, 'SimCity 2000', 'simcity-2000', 'multi'),
    (609, 'SimCity 3000', 'simcity-3000', 'multi'),
    (610, 'SimCity 4', 'simcity-4', 'multi'),
    (611, 'Cities: Skylines', 'cities-skylines', 'multi'),
    (612, 'Cities: Skylines II', 'cities-skylines-ii', 'multi'),
    (613, 'Cities XL', 'cities-xl', 'multi'),
    (614, 'Planet Zoo', 'planet-zoo', 'multi'),
    (615, 'Planet Coaster', 'planet-coaster', 'multi'),
    (616, 'Planet Coaster 2', 'planet-coaster-2', 'multi'),
    (617, 'Jurassic World Evolution', 'jurassic-world-evolution', 'multi'),
    (618, 'Jurassic World Evolution 2', 'jurassic-world-evolution-2', 'multi'),
    (619, 'Zoo Tycoon', 'zoo-tycoon', 'multi'),
    (620, 'RollerCoaster Tycoon', 'rollercoaster-tycoon', 'multi'),
    (621, 'RollerCoaster Tycoon 2', 'rollercoaster-tycoon-2', 'multi'),
    (622, 'RollerCoaster Tycoon 3', 'rollercoaster-tycoon-3', 'multi'),
    (623, 'Two Point Hospital', 'two-point-hospital', 'multi'),
    (624, 'Two Point Campus', 'two-point-campus', 'multi'),
    (625, 'Farming Simulator 17', 'farming-simulator-17', 'multi'),
    (626, 'Farming Simulator 19', 'farming-simulator-19', 'multi'),
    (627, 'Farming Simulator 22', 'farming-simulator-22', 'multi'),
    (628, 'Farming Simulator 25', 'farming-simulator-25', 'multi'),
    (629, 'American Truck Simulator', 'american-truck-simulator', 'multi'),
    (630, 'Train Simulator Classic', 'train-simulator-classic', 'multi'),
    (631, 'Train Sim World', 'train-sim-world', 'multi'),
    (632, 'Train Sim World 2', 'train-sim-world-2', 'multi'),
    (633, 'Train Sim World 3', 'train-sim-world-3', 'multi'),
    (634, 'Train Sim World 4', 'train-sim-world-4', 'multi'),
    (635, 'Train Sim World 5', 'train-sim-world-5', 'multi'),
    (636, 'Derail Valley', 'derail-valley', 'multi'),
    (637, 'SnowRunner', 'snowrunner', 'multi'),
    (638, 'MudRunner', 'mudrunner', 'multi'),
    (639, 'Spintires', 'spintires', 'multi'),
    (640, 'BeamNG.drive', 'beamng-drive', 'multi'),
    (641, 'CarX Drift Racing Online', 'carx-drift-racing-online', 'multi'),
    (642, 'Assetto Corsa', 'assetto-corsa', 'multi'),
    (643, 'Assetto Corsa Competizione', 'assetto-corsa-competizione', 'multi'),
    (644, 'Automobilista', 'automobilista', 'multi'),
    (645, 'Automobilista 2', 'automobilista-2', 'multi'),
    (646, 'iRacing', 'iracing', 'multi'),
    (647, 'rFactor', 'rfactor', 'multi'),
    (648, 'rFactor 2', 'rfactor-2', 'multi'),
    (649, 'Project CARS', 'project-cars', 'multi'),
    (650, 'Project CARS 2', 'project-cars-2', 'multi'),
    (651, 'Project CARS 3', 'project-cars-3', 'multi'),
    (652, 'Dirt Rally', 'dirt-rally', 'multi'),
    (653, 'Dirt Rally 2.0', 'dirt-rally-2-0', 'multi'),
    (654, 'Dirt 3', 'dirt-3', 'multi'),
    (655, 'Dirt 4', 'dirt-4', 'multi'),
    (656, 'Dirt 5', 'dirt-5', 'multi'),
    (657, 'WRC 7', 'wrc-7', 'multi'),
    (658, 'WRC 8', 'wrc-8', 'multi'),
    (659, 'WRC 9', 'wrc-9', 'multi'),
    (660, 'WRC 10', 'wrc-10', 'multi'),
    (661, 'EA Sports WRC', 'ea-sports-wrc', 'multi'),
    (662, 'F1 2018', 'f1-2018', 'multi'),
    (663, 'F1 2019', 'f1-2019', 'multi'),
    (664, 'F1 2020', 'f1-2020', 'multi'),
    (665, 'F1 2021', 'f1-2021', 'multi'),
    (666, 'F1 22', 'f1-22', 'multi'),
    (667, 'F1 23', 'f1-23', 'multi'),
    (668, 'F1 24', 'f1-24', 'multi'),
    (669, 'F1 25', 'f1-25', 'multi'),
    (670, 'MotoGP 20', 'motogp-20', 'multi'),
    (671, 'MotoGP 21', 'motogp-21', 'multi'),
    (672, 'MotoGP 22', 'motogp-22', 'multi'),
    (673, 'MotoGP 23', 'motogp-23', 'multi'),
    (674, 'MotoGP 24', 'motogp-24', 'multi'),
    (675, 'MotoGP 25', 'motogp-25', 'multi'),
    (676, 'WWE 2K19', 'wwe-2k19', 'multi'),
    (677, 'WWE 2K20', 'wwe-2k20', 'multi'),
    (678, 'WWE 2K22', 'wwe-2k22', 'multi'),
    (679, 'WWE 2K23', 'wwe-2k23', 'multi'),
    (680, 'WWE 2K24', 'wwe-2k24', 'multi'),
    (681, 'WWE 2K25', 'wwe-2k25', 'multi'),
    (682, 'NBA 2K19', 'nba-2k19', 'multi'),
    (683, 'NBA 2K20', 'nba-2k20', 'multi'),
    (684, 'NBA 2K21', 'nba-2k21', 'multi'),
    (685, 'NBA 2K22', 'nba-2k22', 'multi'),
    (686, 'NBA 2K23', 'nba-2k23', 'multi'),
    (687, 'NBA 2K24', 'nba-2k24', 'multi'),
    (688, 'NBA 2K25', 'nba-2k25', 'multi'),
    (689, 'EA Sports FC 24', 'ea-sports-fc-24', 'multi'),
    (690, 'EA Sports FC 25', 'ea-sports-fc-25', 'multi'),
    (691, 'EA Sports FC 26', 'ea-sports-fc-26', 'multi'),
    (692, 'FIFA 19', 'fifa-19', 'multi'),
    (693, 'FIFA 20', 'fifa-20', 'multi'),
    (694, 'FIFA 21', 'fifa-21', 'multi'),
    (695, 'FIFA 22', 'fifa-22', 'multi'),
    (696, 'FIFA 23', 'fifa-23', 'multi'),
    (697, 'eFootball', 'efootball', 'multi'),
    (698, 'eFootball 2024', 'efootball-2024', 'multi'),
    (699, 'eFootball 2025', 'efootball-2025', 'multi'),
    (700, 'Madden NFL 22', 'madden-nfl-22', 'multi'),
    (701, 'Madden NFL 23', 'madden-nfl-23', 'multi'),
    (702, 'Madden NFL 24', 'madden-nfl-24', 'multi'),
    (703, 'Madden NFL 25', 'madden-nfl-25', 'multi'),
    (704, 'NHL 22', 'nhl-22', 'multi'),
    (705, 'NHL 23', 'nhl-23', 'multi'),
    (706, 'NHL 24', 'nhl-24', 'multi'),
    (707, 'NHL 25', 'nhl-25', 'multi'),
    (708, 'MLB The Show 22', 'mlb-the-show-22', 'multi'),
    (709, 'MLB The Show 23', 'mlb-the-show-23', 'multi'),
    (710, 'MLB The Show 24', 'mlb-the-show-24', 'multi'),
    (711, 'MLB The Show 25', 'mlb-the-show-25', 'multi'),
    (712, 'Tony Hawk''s Pro Skater 1 + 2', 'tony-hawks-pro-skater-1-2', 'multi'),
    (713, 'Tony Hawk''s Pro Skater 3 + 4', 'tony-hawks-pro-skater-3-4', 'multi'),
    (714, 'Skate', 'skate', 'multi'),
    (715, 'Skate 2', 'skate-2', 'multi'),
    (716, 'Skate 3', 'skate-3', 'multi'),
    (717, 'Skate 4', 'skate-4', 'multi'),
    (718, 'SSX', 'ssx', 'multi'),
    (719, 'SSX 3', 'ssx-3', 'multi'),
    (720, 'Burnout Paradise', 'burnout-paradise', 'multi'),
    (721, 'Need for Speed Underground', 'need-for-speed-underground', 'multi'),
    (722, 'Need for Speed Underground 2', 'need-for-speed-underground-2', 'multi'),
    (723, 'Need for Speed Most Wanted', 'need-for-speed-most-wanted', 'multi'),
    (724, 'Need for Speed Carbon', 'need-for-speed-carbon', 'multi'),
    (725, 'Need for Speed ProStreet', 'need-for-speed-prostreet', 'multi'),
    (726, 'Need for Speed Undercover', 'need-for-speed-undercover', 'multi'),
    (727, 'Need for Speed Shift', 'need-for-speed-shift', 'multi'),
    (728, 'Need for Speed Hot Pursuit', 'need-for-speed-hot-pursuit', 'multi'),
    (729, 'Need for Speed Rivals', 'need-for-speed-rivals', 'multi'),
    (730, 'Need for Speed Payback', 'need-for-speed-payback', 'multi'),
    (731, 'Need for Speed Heat', 'need-for-speed-heat', 'multi'),
    (732, 'Need for Speed Unbound', 'need-for-speed-unbound', 'multi'),
    (733, 'Midnight Club 2', 'midnight-club-2', 'multi'),
    (734, 'Midnight Club 3: DUB Edition', 'midnight-club-3-dub-edition', 'multi'),
    (735, 'Midnight Club: Los Angeles', 'midnight-club-los-angeles', 'multi'),
    (736, 'The Simpsons: Hit & Run', 'the-simpsons-hit-and-run', 'multi'),
    (737, 'Spider-Man', 'spider-man', 'multi'),
    (738, 'Spider-Man 2', 'spider-man-2', 'multi'),
    (739, 'Marvel''s Spider-Man Remastered', 'marvels-spider-man-remastered', 'multi'),
    (740, 'Marvel''s Spider-Man: Miles Morales', 'marvels-spider-man-miles-morales', 'multi'),
    (741, 'Marvel''s Spider-Man 2', 'marvels-spider-man-2', 'multi'),
    (742, 'Marvel''s Guardians of the Galaxy', 'marvels-guardians-of-the-galaxy', 'multi'),
    (743, 'Marvel''s Avengers', 'marvels-avengers', 'multi'),
    (744, 'Marvel Ultimate Alliance', 'marvel-ultimate-alliance', 'multi'),
    (745, 'Marvel Ultimate Alliance 2', 'marvel-ultimate-alliance-2', 'multi'),
    (746, 'Marvel Ultimate Alliance 3', 'marvel-ultimate-alliance-3', 'multi'),
    (747, 'X-Men Legends', 'x-men-legends', 'multi'),
    (748, 'X-Men Legends II', 'x-men-legends-ii', 'multi'),
    (749, 'Deadpool', 'deadpool', 'multi'),
    (750, 'Midnight Suns', 'midnight-suns', 'multi'),
    (751, 'Batman: Arkham Asylum', 'batman-arkham-asylum', 'multi'),
    (752, 'Batman: Arkham City', 'batman-arkham-city', 'multi'),
    (753, 'Batman: Arkham Origins', 'batman-arkham-origins', 'multi'),
    (754, 'Batman: Arkham Knight', 'batman-arkham-knight', 'multi'),
    (755, 'Batman: Arkham Shadow', 'batman-arkham-shadow', 'multi'),
    (756, 'Middle-earth: Shadow of Mordor', 'middle-earth-shadow-of-mordor', 'multi'),
    (757, 'Middle-earth: Shadow of War', 'middle-earth-shadow-of-war', 'multi'),
    (758, 'Mad Max', 'mad-max', 'multi'),
    (759, 'The Matrix: Path of Neo', 'the-matrix-path-of-neo', 'multi'),
    (760, 'LEGO Star Wars: The Complete Saga', 'lego-star-wars-the-complete-saga', 'multi'),
    (761, 'LEGO Star Wars: The Skywalker Saga', 'lego-star-wars-the-skywalker-saga', 'multi'),
    (762, 'LEGO Marvel Super Heroes', 'lego-marvel-super-heroes', 'multi'),
    (763, 'LEGO Marvel Super Heroes 2', 'lego-marvel-super-heroes-2', 'multi'),
    (764, 'LEGO Batman: The Videogame', 'lego-batman-the-videogame', 'multi'),
    (765, 'LEGO Batman 2: DC Super Heroes', 'lego-batman-2-dc-super-heroes', 'multi'),
    (766, 'LEGO Batman 3: Beyond Gotham', 'lego-batman-3-beyond-gotham', 'multi'),
    (767, 'LEGO DC Super-Villains', 'lego-dc-super-villains', 'multi'),
    (768, 'LEGO Harry Potter: Years 1–4', 'lego-harry-potter-years-1-4', 'multi'),
    (769, 'LEGO Harry Potter: Years 5–7', 'lego-harry-potter-years-5-7', 'multi'),
    (770, 'LEGO The Hobbit', 'lego-the-hobbit', 'multi'),
    (771, 'LEGO Jurassic World', 'lego-jurassic-world', 'multi'),
    (772, 'LEGO City Undercover', 'lego-city-undercover', 'multi'),
    (773, 'LEGO The Incredibles', 'lego-the-incredibles', 'multi'),
    (774, 'LEGO Pirates of the Caribbean', 'lego-pirates-of-the-caribbean', 'multi'),
    (775, 'LEGO Indiana Jones', 'lego-indiana-jones', 'multi'),
    (776, 'LEGO Indiana Jones 2', 'lego-indiana-jones-2', 'multi'),
    (777, 'LEGO Lord of the Rings', 'lego-lord-of-the-rings', 'multi'),
    (778, 'Hogwarts Legacy', 'hogwarts-legacy', 'multi'),
    (779, 'Harry Potter: Quidditch Champions', 'harry-potter-quidditch-champions', 'multi'),
    (780, 'Shadow of the Tomb Raider', 'shadow-of-the-tomb-raider', 'multi'),
    (781, 'Rise of the Tomb Raider', 'rise-of-the-tomb-raider', 'multi'),
    (782, 'Tomb Raider (2013)', 'tomb-raider-2013', 'multi'),
    (783, 'Tomb Raider: Anniversary', 'tomb-raider-anniversary', 'multi'),
    (784, 'Tomb Raider: Legend', 'tomb-raider-legend', 'multi'),
    (785, 'Tomb Raider: Underworld', 'tomb-raider-underworld', 'multi'),
    (786, 'Tomb Raider I–III Remastered', 'tomb-raider-i-iii-remastered', 'multi'),
    (787, 'Uncharted: Legacy of Thieves Collection', 'uncharted-legacy-of-thieves-collection', 'multi'),
    (788, 'Uncharted 4: A Thief''s End', 'uncharted-4-a-thiefs-end', 'multi'),
    (789, 'Uncharted: The Lost Legacy', 'uncharted-the-lost-legacy', 'multi'),
    (790, 'The Last of Us Part I', 'the-last-of-us-part-i', 'multi'),
    (791, 'The Last of Us Part II Remastered', 'the-last-of-us-part-ii-remastered', 'multi'),
    (792, 'Days Gone', 'days-gone', 'multi'),
    (793, 'Horizon Zero Dawn', 'horizon-zero-dawn', 'multi'),
    (794, 'Horizon Forbidden West', 'horizon-forbidden-west', 'multi'),
    (795, 'Ghost of Tsushima', 'ghost-of-tsushima', 'multi'),
    (796, 'God of War', 'god-of-war', 'multi'),
    (797, 'God of War Ragnarök', 'god-of-war-ragnar-k', 'multi'),
    (798, 'Ratchet & Clank: Rift Apart', 'ratchet-and-clank-rift-apart', 'multi'),
    (799, 'Returnal', 'returnal', 'multi'),
    (800, 'Helldivers', 'helldivers', 'multi'),
    (801, 'Helldivers 2', 'helldivers-2', 'multi'),
    (802, 'Death Stranding', 'death-stranding', 'multi'),
    (803, 'Death Stranding Director''s Cut', 'death-stranding-directors-cut', 'multi'),
    (804, 'Shadow of the Colossus', 'shadow-of-the-colossus', 'multi'),
    (805, 'The Last Guardian', 'the-last-guardian', 'multi'),
    (806, 'Infamous', 'infamous', 'multi'),
    (807, 'Infamous 2', 'infamous-2', 'multi'),
    (808, 'LittleBigPlanet', 'littlebigplanet', 'multi'),
    (809, 'LittleBigPlanet 2', 'littlebigplanet-2', 'multi'),
    (810, 'Dreams', 'dreams', 'multi'),
    (811, 'Gran Turismo', 'gran-turismo', 'multi'),
    (812, 'Gran Turismo 2', 'gran-turismo-2', 'multi'),
    (813, 'Gran Turismo 3: A-Spec', 'gran-turismo-3-a-spec', 'multi'),
    (814, 'Gran Turismo 4', 'gran-turismo-4', 'multi'),
    (815, 'Gran Turismo 5', 'gran-turismo-5', 'multi'),
    (816, 'Gran Turismo 6', 'gran-turismo-6', 'multi'),
    (817, 'Gran Turismo 7', 'gran-turismo-7', 'multi'),
    (818, 'God of War III', 'god-of-war-iii', 'multi'),
    (819, 'Killzone', 'killzone', 'multi'),
    (820, 'Killzone 2', 'killzone-2', 'multi'),
    (821, 'Killzone 3', 'killzone-3', 'multi'),
    (822, 'Resistance: Fall of Man', 'resistance-fall-of-man', 'multi'),
    (823, 'Resistance 2', 'resistance-2', 'multi'),
    (824, 'Resistance 3', 'resistance-3', 'multi'),
    (825, 'Knack', 'knack', 'multi'),
    (826, 'Knack 2', 'knack-2', 'multi'),
    (827, 'The Order: 1886', 'the-order-1886', 'multi'),
    (828, 'Sackboy: A Big Adventure', 'sackboy-a-big-adventure', 'multi'),
    (829, 'Astro''s Playroom', 'astros-playroom', 'multi'),
    (830, 'Astro Bot', 'astro-bot', 'multi'),
    (831, 'Nintendo Switch Sports', 'nintendo-switch-sports', 'multi'),
    (832, 'Mario Kart 8 Deluxe', 'mario-kart-8-deluxe', 'multi'),
    (833, 'Mario Kart 8', 'mario-kart-8', 'multi'),
    (834, 'Mario Kart Wii', 'mario-kart-wii', 'multi'),
    (835, 'Mario Kart DS', 'mario-kart-ds', 'multi'),
    (836, 'Mario Kart 7', 'mario-kart-7', 'multi'),
    (837, 'Super Mario Bros.', 'super-mario-bros', 'multi'),
    (838, 'Super Mario Bros. 3', 'super-mario-bros-3', 'multi'),
    (839, 'Super Mario World', 'super-mario-world', 'multi'),
    (840, 'Super Mario 64', 'super-mario-64', 'multi'),
    (841, 'Super Mario Sunshine', 'super-mario-sunshine', 'multi'),
    (842, 'Super Mario Galaxy', 'super-mario-galaxy', 'multi'),
    (843, 'Super Mario Galaxy 2', 'super-mario-galaxy-2', 'multi'),
    (844, 'Super Mario Odyssey', 'super-mario-odyssey', 'multi'),
    (845, 'Super Mario 3D World', 'super-mario-3d-world', 'multi'),
    (846, 'Super Mario 3D Land', 'super-mario-3d-land', 'multi'),
    (847, 'Super Mario Maker', 'super-mario-maker', 'multi'),
    (848, 'Super Mario Maker 2', 'super-mario-maker-2', 'multi'),
    (849, 'The Legend of Zelda', 'the-legend-of-zelda', 'multi'),
    (850, 'Zelda II: The Adventure of Link', 'zelda-ii-the-adventure-of-link', 'multi'),
    (851, 'The Legend of Zelda: A Link to the Past', 'the-legend-of-zelda-a-link-to-the-past', 'multi'),
    (852, 'The Legend of Zelda: Ocarina of Time', 'the-legend-of-zelda-ocarina-of-time', 'multi'),
    (853, 'The Legend of Zelda: Majora''s Mask', 'the-legend-of-zelda-majoras-mask', 'multi'),
    (854, 'The Legend of Zelda: The Wind Waker', 'the-legend-of-zelda-the-wind-waker', 'multi'),
    (855, 'The Legend of Zelda: Twilight Princess', 'the-legend-of-zelda-twilight-princess', 'multi'),
    (856, 'The Legend of Zelda: Skyward Sword', 'the-legend-of-zelda-skyward-sword', 'multi'),
    (857, 'The Legend of Zelda: Breath of the Wild', 'the-legend-of-zelda-breath-of-the-wild', 'multi'),
    (858, 'The Legend of Zelda: Tears of the Kingdom', 'the-legend-of-zelda-tears-of-the-kingdom', 'multi'),
    (859, 'Metroid', 'metroid', 'multi'),
    (860, 'Super Metroid', 'super-metroid', 'multi'),
    (861, 'Metroid Prime', 'metroid-prime', 'multi'),
    (862, 'Metroid Prime 2: Echoes', 'metroid-prime-2-echoes', 'multi'),
    (863, 'Metroid Prime 3: Corruption', 'metroid-prime-3-corruption', 'multi'),
    (864, 'Metroid Dread', 'metroid-dread', 'multi'),
    (865, 'Pokémon Red', 'pok-mon-red', 'multi'),
    (866, 'Pokémon Blue', 'pok-mon-blue', 'multi'),
    (867, 'Pokémon Yellow', 'pok-mon-yellow', 'multi'),
    (868, 'Pokémon Gold', 'pok-mon-gold', 'multi'),
    (869, 'Pokémon Silver', 'pok-mon-silver', 'multi'),
    (870, 'Pokémon Crystal', 'pok-mon-crystal', 'multi'),
    (871, 'Pokémon Ruby', 'pok-mon-ruby', 'multi'),
    (872, 'Pokémon Sapphire', 'pok-mon-sapphire', 'multi'),
    (873, 'Pokémon Emerald', 'pok-mon-emerald', 'multi'),
    (874, 'Pokémon Diamond', 'pok-mon-diamond', 'multi'),
    (875, 'Pokémon Pearl', 'pok-mon-pearl', 'multi'),
    (876, 'Pokémon Platinum', 'pok-mon-platinum', 'multi'),
    (877, 'Pokémon Black', 'pok-mon-black', 'multi'),
    (878, 'Pokémon White', 'pok-mon-white', 'multi'),
    (879, 'Pokémon Black 2', 'pok-mon-black-2', 'multi'),
    (880, 'Pokémon White 2', 'pok-mon-white-2', 'multi'),
    (881, 'Pokémon X', 'pok-mon-x', 'multi'),
    (882, 'Pokémon Y', 'pok-mon-y', 'multi'),
    (883, 'Pokémon Sun', 'pok-mon-sun', 'multi'),
    (884, 'Pokémon Moon', 'pok-mon-moon', 'multi'),
    (885, 'Pokémon Ultra Sun', 'pok-mon-ultra-sun', 'multi'),
    (886, 'Pokémon Ultra Moon', 'pok-mon-ultra-moon', 'multi'),
    (887, 'Pokémon Sword', 'pok-mon-sword', 'multi'),
    (888, 'Pokémon Shield', 'pok-mon-shield', 'multi'),
    (889, 'Pokémon Scarlet', 'pok-mon-scarlet', 'multi'),
    (890, 'Pokémon Violet', 'pok-mon-violet', 'multi'),
    (891, 'Pokémon Legends: Arceus', 'pok-mon-legends-arceus', 'multi'),
    (892, 'Pokémon Let''s Go, Pikachu!', 'pok-mon-lets-go-pikachu', 'multi'),
    (893, 'Pokémon Let''s Go, Eevee!', 'pok-mon-lets-go-eevee', 'multi'),
    (894, 'Super Smash Bros.', 'super-smash-bros', 'multi'),
    (895, 'Super Smash Bros. Melee', 'super-smash-bros-melee', 'multi'),
    (896, 'Super Smash Bros. Brawl', 'super-smash-bros-brawl', 'multi'),
    (897, 'Super Smash Bros. for Nintendo 3DS', 'super-smash-bros-for-nintendo-3ds', 'multi'),
    (898, 'Super Smash Bros. Ultimate', 'super-smash-bros-ultimate', 'multi'),
    (899, 'Splatoon', 'splatoon', 'multi'),
    (900, 'Splatoon 2', 'splatoon-2', 'multi'),
    (901, 'Splatoon 3', 'splatoon-3', 'multi'),
    (902, 'Animal Crossing: New Horizons', 'animal-crossing-new-horizons', 'multi'),
    (903, 'Animal Crossing: New Leaf', 'animal-crossing-new-leaf', 'multi'),
    (904, 'Fire Emblem: Three Houses', 'fire-emblem-three-houses', 'multi'),
    (905, 'Fire Emblem Engage', 'fire-emblem-engage', 'multi'),
    (906, 'Xenoblade Chronicles', 'xenoblade-chronicles', 'multi'),
    (907, 'Xenoblade Chronicles 2', 'xenoblade-chronicles-2', 'multi'),
    (908, 'Xenoblade Chronicles 3', 'xenoblade-chronicles-3', 'multi'),
    (909, 'Xenoblade Chronicles X', 'xenoblade-chronicles-x', 'multi'),
    (910, 'Kirby and the Forgotten Land', 'kirby-and-the-forgotten-land', 'multi'),
    (911, 'Kirby: Planet Robobot', 'kirby-planet-robobot', 'multi'),
    (912, 'Kirby Star Allies', 'kirby-star-allies', 'multi'),
    (913, 'Pikmin 3 Deluxe', 'pikmin-3-deluxe', 'multi'),
    (914, 'Pikmin 4', 'pikmin-4', 'multi'),
    (915, 'Bayonetta', 'bayonetta', 'multi'),
    (916, 'Bayonetta 2', 'bayonetta-2', 'multi'),
    (917, 'Bayonetta 3', 'bayonetta-3', 'multi'),
    (918, 'Astral Chain', 'astral-chain', 'multi'),
    (919, 'The Wonderful 101: Remastered', 'the-wonderful-101-remastered', 'multi'),
    (920, 'Daemon X Machina', 'daemon-x-machina', 'multi'),
    (921, 'Monster Hunter Stories', 'monster-hunter-stories', 'multi'),
    (922, 'Monster Hunter Stories 2: Wings of Ruin', 'monster-hunter-stories-2-wings-of-ruin', 'multi'),
    (923, 'Catherine Classic', 'catherine-classic', 'multi'),
    (924, 'Catherine: Full Body', 'catherine-full-body', 'multi'),
    (925, '13 Sentinels: Aegis Rim', '13-sentinels-aegis-rim', 'multi'),
    (926, 'Valkyria Chronicles', 'valkyria-chronicles', 'multi'),
    (927, 'Valkyria Chronicles 4', 'valkyria-chronicles-4', 'multi'),
    (928, 'Danganronpa: Trigger Happy Havoc', 'danganronpa-trigger-happy-havoc', 'multi'),
    (929, 'Danganronpa 2: Goodbye Despair', 'danganronpa-2-goodbye-despair', 'multi'),
    (930, 'Danganronpa V3: Killing Harmony', 'danganronpa-v3-killing-harmony', 'multi'),
    (931, 'Zero Escape: The Nonary Games', 'zero-escape-the-nonary-games', 'multi'),
    (932, 'AI: The Somnium Files', 'ai-the-somnium-files', 'multi'),
    (933, 'AI: The Somnium Files – nirvanA Initiative', 'ai-the-somnium-files-nirvana-initiative', 'multi'),
    (934, 'Ace Attorney Trilogy', 'ace-attorney-trilogy', 'multi'),
    (935, 'The Great Ace Attorney Chronicles', 'the-great-ace-attorney-chronicles', 'multi'),
    (936, 'Phoenix Wright: Ace Attorney – Spirit of Justice', 'phoenix-wright-ace-attorney-spirit-of-justice', 'multi'),
    (937, 'Professor Layton and the Curious Village', 'professor-layton-and-the-curious-village', 'multi'),
    (938, 'Professor Layton and the Diabolical Box', 'professor-layton-and-the-diabolical-box', 'multi'),
    (939, 'Professor Layton and the Unwound Future', 'professor-layton-and-the-unwound-future', 'multi'),
    (940, 'Professor Layton vs. Phoenix Wright', 'professor-layton-vs-phoenix-wright', 'multi'),
    (941, 'Nier: Automata', 'nier-automata', 'multi'),
    (942, 'Vanquish', 'vanquish', 'multi'),
    (943, 'Metal Gear Solid', 'metal-gear-solid', 'multi'),
    (944, 'Metal Gear Solid 2: Sons of Liberty', 'metal-gear-solid-2-sons-of-liberty', 'multi'),
    (945, 'Metal Gear Solid 3: Snake Eater', 'metal-gear-solid-3-snake-eater', 'multi'),
    (946, 'Metal Gear Solid 4: Guns of the Patriots', 'metal-gear-solid-4-guns-of-the-patriots', 'multi'),
    (947, 'Metal Gear Solid V: Ground Zeroes', 'metal-gear-solid-v-ground-zeroes', 'multi'),
    (948, 'Metal Gear Solid V: The Phantom Pain', 'metal-gear-solid-v-the-phantom-pain', 'multi'),
    (949, 'Metal Gear Rising: Revengeance', 'metal-gear-rising-revengeance', 'multi'),
    (950, 'Metal Gear Solid Delta: Snake Eater', 'metal-gear-solid-delta-snake-eater', 'multi'),
    (951, 'Ninja Gaiden', 'ninja-gaiden', 'multi'),
    (952, 'Ninja Gaiden II', 'ninja-gaiden-ii', 'multi'),
    (953, 'Ninja Gaiden 3', 'ninja-gaiden-3', 'multi'),
    (954, 'Ninja Gaiden: Master Collection', 'ninja-gaiden-master-collection', 'multi'),
    (955, 'Darksiders', 'darksiders', 'multi'),
    (956, 'Darksiders II', 'darksiders-ii', 'multi'),
    (957, 'Darksiders III', 'darksiders-iii', 'multi'),
    (958, 'Darksiders Genesis', 'darksiders-genesis', 'multi'),
    (959, 'Prototype', 'prototype', 'multi'),
    (960, 'Prototype 2', 'prototype-2', 'multi'),
    (961, 'Infamous: Second Son', 'infamous-second-son', 'multi'),
    (962, 'Sleeping Dogs', 'sleeping-dogs', 'multi'),
    (963, 'Sleeping Dogs: Definitive Edition', 'sleeping-dogs-definitive-edition', 'multi'),
    (964, 'Saints Row', 'saints-row', 'multi'),
    (965, 'Saints Row 2', 'saints-row-2', 'multi'),
    (966, 'Saints Row: The Third', 'saints-row-the-third', 'multi'),
    (967, 'Saints Row IV', 'saints-row-iv', 'multi'),
    (968, 'Saints Row: Gat out of Hell', 'saints-row-gat-out-of-hell', 'multi'),
    (969, 'Saints Row (2022)', 'saints-row-2022', 'multi'),
    (970, 'Just Cause', 'just-cause', 'multi'),
    (971, 'Just Cause 2', 'just-cause-2', 'multi'),
    (972, 'Just Cause 3', 'just-cause-3', 'multi'),
    (973, 'Just Cause 4', 'just-cause-4', 'multi'),
    (974, 'Mafia', 'mafia', 'multi'),
    (975, 'Mafia II', 'mafia-ii', 'multi'),
    (976, 'Mafia III', 'mafia-iii', 'multi'),
    (977, 'Mafia: Definitive Edition', 'mafia-definitive-edition', 'multi'),
    (978, 'Mafia II: Definitive Edition', 'mafia-ii-definitive-edition', 'multi'),
    (979, 'Mafia III: Definitive Edition', 'mafia-iii-definitive-edition', 'multi'),
    (980, 'GreedFall', 'greedfall', 'multi'),
    (981, 'GreedFall 2', 'greedfall-2', 'multi'),
    (982, 'Kingdoms of Amalur: Reckoning', 'kingdoms-of-amalur-reckoning', 'multi'),
    (983, 'Kingdoms of Amalur: Re-Reckoning', 'kingdoms-of-amalur-re-reckoning', 'multi'),
    (984, 'Fable', 'fable', 'multi'),
    (985, 'Fable II', 'fable-ii', 'multi'),
    (986, 'Fable III', 'fable-iii', 'multi'),
    (987, 'Fable Anniversary', 'fable-anniversary', 'multi'),
    (988, 'Fable (2025)', 'fable-2025', 'multi'),
    (989, 'South Park: The Stick of Truth', 'south-park-the-stick-of-truth', 'multi'),
    (990, 'South Park: The Fractured but Whole', 'south-park-the-fractured-but-whole', 'multi'),
    (991, 'The Stick of Truth', 'the-stick-of-truth', 'multi'),
    (992, 'The Fractured but Whole', 'the-fractured-but-whole', 'multi'),
    (993, 'Mount & Blade: Warband', 'mount-and-blade-warband', 'multi'),
    (994, 'Mount & Blade II: Bannerlord', 'mount-and-blade-ii-bannerlord', 'multi'),
    (995, 'Kenshi', 'kenshi', 'multi'),
    (996, 'RimWorld', 'rimworld', 'multi'),
    (997, 'Dwarf Fortress', 'dwarf-fortress', 'multi'),
    (998, 'Factorio', 'factorio', 'multi'),
    (999, 'Satisfactory', 'satisfactory', 'multi'),
    (1000, 'Dyson Sphere Program', 'dyson-sphere-program', 'multi'),
    (1001, 'Oxygen Not Included', 'oxygen-not-included', 'multi'),
    (1002, 'Frostpunk', 'frostpunk', 'multi'),
    (1003, 'Frostpunk 2', 'frostpunk-2', 'multi'),
    (1004, 'Banished', 'banished', 'multi'),
    (1005, 'Against the Storm', 'against-the-storm', 'multi'),
    (1006, 'Timberborn', 'timberborn', 'multi'),
    (1007, 'Manor Lords', 'manor-lords', 'multi'),
    (1008, 'Workers & Resources: Soviet Republic', 'workers-and-resources-soviet-republic', 'multi'),
    (1009, 'Anno 1800', 'anno-1800', 'multi'),
    (1010, 'Anno 2205', 'anno-2205', 'multi'),
    (1011, 'Anno 2070', 'anno-2070', 'multi'),
    (1012, 'Tropico 4', 'tropico-4', 'multi'),
    (1013, 'Tropico 5', 'tropico-5', 'multi'),
    (1014, 'Tropico 6', 'tropico-6', 'multi'),
    (1015, 'Prison Architect', 'prison-architect', 'multi'),
    (1016, 'Project Zomboid', 'project-zomboid', 'multi'),
    (1017, 'This War of Mine', 'this-war-of-mine', 'multi'),
    (1018, 'They Are Billions', 'they-are-billions', 'multi'),
    (1019, 'Surviving Mars', 'surviving-mars', 'multi'),
    (1020, 'Surviving the Aftermath', 'surviving-the-aftermath', 'multi'),
    (1021, 'Ixion', 'ixion', 'multi'),
    (1022, 'Endzone: A World Apart', 'endzone-a-world-apart', 'multi'),
    (1023, 'Northgard', 'northgard', 'multi'),
    (1024, 'Dorfromantik', 'dorfromantik', 'multi'),
    (1025, 'Mini Metro', 'mini-metro', 'multi'),
    (1026, 'Mini Motorways', 'mini-motorways', 'multi'),
    (1027, 'PowerWash Simulator', 'powerwash-simulator', 'multi'),
    (1028, 'House Flipper', 'house-flipper', 'multi'),
    (1029, 'House Flipper 2', 'house-flipper-2', 'multi'),
    (1030, 'PC Building Simulator', 'pc-building-simulator', 'multi'),
    (1031, 'PC Building Simulator 2', 'pc-building-simulator-2', 'multi'),
    (1032, 'Cooking Simulator', 'cooking-simulator', 'multi'),
    (1033, 'Car Mechanic Simulator 2018', 'car-mechanic-simulator-2018', 'multi'),
    (1034, 'Car Mechanic Simulator 2021', 'car-mechanic-simulator-2021', 'multi'),
    (1035, 'Gas Station Simulator', 'gas-station-simulator', 'multi'),
    (1036, 'Internet Cafe Simulator', 'internet-cafe-simulator', 'multi'),
    (1037, 'Internet Cafe Simulator 2', 'internet-cafe-simulator-2', 'multi'),
    (1038, 'The Long Drive', 'the-long-drive', 'multi'),
    (1039, 'My Summer Car', 'my-summer-car', 'multi'),
    (1040, 'Wreckfest', 'wreckfest', 'multi'),
    (1041, 'FlatOut', 'flatout', 'multi'),
    (1042, 'FlatOut 2', 'flatout-2', 'multi'),
    (1043, 'FlatOut 3: Chaos & Destruction', 'flatout-3-chaos-and-destruction', 'multi'),
    (1044, 'FlatOut 4: Total Insanity', 'flatout-4-total-insanity', 'multi'),
    (1045, 'Burnout Paradise Remastered', 'burnout-paradise-remastered', 'multi'),
    (1046, 'GRID', 'grid', 'multi'),
    (1047, 'GRID 2', 'grid-2', 'multi'),
    (1048, 'GRID Autosport', 'grid-autosport', 'multi'),
    (1049, 'GRID Legends', 'grid-legends', 'multi'),
    (1050, 'F1 Manager 2022', 'f1-manager-2022', 'multi'),
    (1051, 'F1 Manager 2023', 'f1-manager-2023', 'multi'),
    (1052, 'F1 Manager 2024', 'f1-manager-2024', 'multi'),
    (1053, 'Football Manager 2022', 'football-manager-2022', 'multi'),
    (1054, 'Football Manager 2023', 'football-manager-2023', 'multi'),
    (1055, 'Football Manager 2024', 'football-manager-2024', 'multi'),
    (1056, 'Football Manager 2025', 'football-manager-2025', 'multi'),
    (1057, 'Crusader Kings II', 'crusader-kings-ii', 'multi'),
    (1058, 'Crusader Kings III', 'crusader-kings-iii', 'multi'),
    (1059, 'Europa Universalis IV', 'europa-universalis-iv', 'multi'),
    (1060, 'Hearts of Iron IV', 'hearts-of-iron-iv', 'multi'),
    (1061, 'Stellaris', 'stellaris', 'multi'),
    (1062, 'Victoria II', 'victoria-ii', 'multi'),
    (1063, 'Victoria 3', 'victoria-3', 'multi'),
    (1064, 'Cities XL Platinum', 'cities-xl-platinum', 'multi'),
    (1065, 'Europa Universalis III', 'europa-universalis-iii', 'multi'),
    (1066, 'Europa Universalis II', 'europa-universalis-ii', 'multi'),
    (1067, 'Hearts of Iron III', 'hearts-of-iron-iii', 'multi'),
    (1068, 'Company of Heroes', 'company-of-heroes', 'multi'),
    (1069, 'Company of Heroes 2', 'company-of-heroes-2', 'multi'),
    (1070, 'Company of Heroes 3', 'company-of-heroes-3', 'multi'),
    (1071, 'Command & Conquer', 'command-and-conquer', 'multi'),
    (1072, 'Command & Conquer: Red Alert', 'command-and-conquer-red-alert', 'multi'),
    (1073, 'Command & Conquer: Red Alert 2', 'command-and-conquer-red-alert-2', 'multi'),
    (1074, 'Command & Conquer: Generals', 'command-and-conquer-generals', 'multi'),
    (1075, 'Command & Conquer 3: Tiberium Wars', 'command-and-conquer-3-tiberium-wars', 'multi'),
    (1076, 'Command & Conquer 4: Tiberian Twilight', 'command-and-conquer-4-tiberian-twilight', 'multi'),
    (1077, 'Warcraft II', 'warcraft-ii', 'multi'),
    (1078, 'Warcraft III', 'warcraft-iii', 'multi'),
    (1079, 'StarCraft', 'starcraft', 'multi'),
    (1080, 'StarCraft II: Wings of Liberty', 'starcraft-ii-wings-of-liberty', 'multi'),
    (1081, 'StarCraft II: Heart of the Swarm', 'starcraft-ii-heart-of-the-swarm', 'multi'),
    (1082, 'StarCraft II: Legacy of the Void', 'starcraft-ii-legacy-of-the-void', 'multi'),
    (1083, 'Age of Empires', 'age-of-empires', 'multi'),
    (1084, 'Age of Empires II', 'age-of-empires-ii', 'multi'),
    (1085, 'Age of Empires III', 'age-of-empires-iii', 'multi'),
    (1086, 'Age of Empires V', 'age-of-empires-v', 'multi'),
    (1087, 'Stronghold', 'stronghold', 'multi'),
    (1088, 'Stronghold Crusader', 'stronghold-crusader', 'multi'),
    (1089, 'Stronghold 2', 'stronghold-2', 'multi'),
    (1090, 'Stronghold Legends', 'stronghold-legends', 'multi'),
    (1091, 'Stronghold Crusader HD', 'stronghold-crusader-hd', 'multi'),
    (1092, 'Stronghold Crusader 2', 'stronghold-crusader-2', 'multi'),
    (1093, 'Stronghold Kingdoms', 'stronghold-kingdoms', 'multi'),
    (1094, 'Company of Heroes: Opposing Fronts', 'company-of-heroes-opposing-fronts', 'multi'),
    (1095, 'Company of Heroes: Tales of Valor', 'company-of-heroes-tales-of-valor', 'multi'),
    (1096, 'Men of War', 'men-of-war', 'multi'),
    (1097, 'Men of War: Assault Squad', 'men-of-war-assault-squad', 'multi'),
    (1098, 'Men of War: Assault Squad 2', 'men-of-war-assault-squad-2', 'multi'),
    (1099, 'Men of War II', 'men-of-war-ii', 'multi'),
    (1100, 'Homeworld', 'homeworld', 'multi')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    source = VALUES(source);

-- ============================================================
-- Curated developer and publisher metadata
-- ============================================================
UPDATE game_catalog
SET
    developer = CASE id
        WHEN 1 THEN 'Riot Games'
        WHEN 2 THEN 'Valve'
        WHEN 3 THEN 'Mojang Studios'
        WHEN 4 THEN 'Rockstar Games'
        WHEN 5 THEN 'Valve'
        WHEN 6 THEN 'Riot Games'
        WHEN 7 THEN 'Infinity Ward'
        WHEN 8 THEN 'SCS Software'
        WHEN 9 THEN 'Respawn Entertainment'
        WHEN 10 THEN 'Epic Games'
        WHEN 11 THEN 'Blizzard Entertainment'
        WHEN 12 THEN 'Psyonix'
        WHEN 13 THEN 'PUBG Studios'
        WHEN 14 THEN 'Ubisoft Montreal'
        WHEN 15 THEN 'Embark Studios'
        WHEN 16 THEN 'Bungie'
        WHEN 17 THEN 'Digital Extremes'
        WHEN 18 THEN 'Grinding Gear Games'
        WHEN 19 THEN 'Grinding Gear Games'
        WHEN 20 THEN 'Valve'
        WHEN 21 THEN 'Facepunch Studios'
        WHEN 22 THEN 'Re-Logic'
        WHEN 23 THEN 'ConcernedApe'
        WHEN 24 THEN 'Facepunch Studios'
        WHEN 25 THEN 'Studio Wildcard'
        WHEN 26 THEN 'Studio Wildcard'
        WHEN 27 THEN 'Bohemia Interactive'
        WHEN 28 THEN 'Smartly Dressed Games'
        WHEN 29 THEN 'The Fun Pimps'
        WHEN 30 THEN 'Klei Entertainment'
        WHEN 31 THEN 'Pocketpair'
        WHEN 32 THEN 'Keen Games'
        WHEN 33 THEN 'Iron Gate Studio'
        WHEN 34 THEN 'Redbeet Interactive'
        WHEN 35 THEN 'Unknown Worlds Entertainment'
        WHEN 36 THEN 'Unknown Worlds Entertainment'
        WHEN 37 THEN 'Endnight Games'
        WHEN 38 THEN 'Endnight Games'
        WHEN 39 THEN 'Obsidian Entertainment'
        WHEN 40 THEN 'System Era Softworks'
        WHEN 41 THEN 'Hello Games'
        WHEN 42 THEN 'Rare'
        WHEN 43 THEN 'Ghost Ship Games'
        WHEN 44 THEN 'Valve'
        WHEN 45 THEN 'Tripwire Interactive'
        WHEN 46 THEN 'Overkill Software'
        WHEN 47 THEN 'Starbreeze Studios'
        WHEN 48 THEN 'Behaviour Interactive'
        WHEN 49 THEN 'Kinetic Games'
        WHEN 50 THEN 'Zeekerss'
        WHEN 51 THEN 'Landfall Games'
        WHEN 52 THEN 'Innersloth'
        WHEN 53 THEN 'Mediatonic'
        WHEN 54 THEN 'No Brakes Games'
        WHEN 55 THEN 'Hazelight Studios'
        WHEN 56 THEN 'Hazelight Studios'
        WHEN 57 THEN 'Coldwood Interactive'
        WHEN 58 THEN 'Coldwood Interactive'
        WHEN 59 THEN 'Valve'
        WHEN 60 THEN 'Valve'
        WHEN 61 THEN 'Valve'
        WHEN 62 THEN 'Valve'
        WHEN 63 THEN 'Valve'
        WHEN 64 THEN 'Valve'
        WHEN 65 THEN 'Valve'
        WHEN 66 THEN 'Crowbar Collective'
        WHEN 67 THEN 'Valve'
        WHEN 68 THEN 'Valve'
        WHEN 69 THEN 'Valve'
        WHEN 70 THEN 'Valve'
        WHEN 71 THEN 'Valve'
        WHEN 72 THEN 'Valve'
        WHEN 73 THEN 'Valve'
        WHEN 74 THEN 'Valve'
        WHEN 75 THEN 'Worlds Edge'
        WHEN 76 THEN 'Worlds Edge'
        WHEN 77 THEN 'Worlds Edge'
        WHEN 78 THEN 'Worlds Edge'
        WHEN 79 THEN 'Blizzard Entertainment'
        WHEN 80 THEN 'Blizzard Entertainment'
        WHEN 81 THEN 'Blizzard Entertainment'
        WHEN 82 THEN 'Blizzard Entertainment'
        WHEN 83 THEN 'Blizzard Entertainment'
        WHEN 84 THEN 'Blizzard Entertainment'
        WHEN 85 THEN 'Blizzard Entertainment'
        WHEN 86 THEN 'Blizzard Entertainment'
        WHEN 87 THEN 'Blizzard Entertainment'
        WHEN 88 THEN 'Blizzard Entertainment'
        WHEN 89 THEN 'Blizzard Entertainment'
        WHEN 90 THEN 'Blizzard Entertainment'
        WHEN 91 THEN 'Blizzard Entertainment'
        WHEN 92 THEN 'ArenaNet'
        WHEN 93 THEN 'ArenaNet'
        WHEN 94 THEN 'ZeniMax Online Studios'
        WHEN 95 THEN 'Bethesda Game Studios'
        WHEN 96 THEN 'Bethesda Game Studios'
        WHEN 97 THEN 'Bethesda Game Studios'
        WHEN 98 THEN 'Bethesda Game Studios'
        WHEN 99 THEN 'Bethesda Game Studios'
        WHEN 100 THEN 'Bethesda Game Studios'
        WHEN 101 THEN 'Bethesda Game Studios'
        WHEN 102 THEN 'Bethesda Game Studios'
        WHEN 103 THEN 'Bethesda Game Studios'
        WHEN 104 THEN 'Bethesda Game Studios'
        WHEN 105 THEN 'Obsidian Entertainment'
        WHEN 106 THEN 'Obsidian Entertainment'
        WHEN 107 THEN 'BioWare'
        WHEN 108 THEN 'BioWare'
        WHEN 109 THEN 'BioWare'
        WHEN 110 THEN 'Larian Studios'
        WHEN 111 THEN 'Larian Studios'
        WHEN 112 THEN 'BioWare'
        WHEN 113 THEN 'BioWare'
        WHEN 114 THEN 'BioWare'
        WHEN 115 THEN 'BioWare'
        WHEN 116 THEN 'BioWare'
        WHEN 117 THEN 'BioWare'
        WHEN 118 THEN 'BioWare'
        WHEN 119 THEN 'BioWare'
        WHEN 120 THEN 'BioWare'
        WHEN 121 THEN 'BioWare'
        WHEN 122 THEN 'Respawn Entertainment'
        WHEN 123 THEN 'Respawn Entertainment'
        WHEN 124 THEN 'Massive Entertainment'
        WHEN 125 THEN 'DICE'
        WHEN 126 THEN 'DICE'
        WHEN 127 THEN 'BioWare'
        WHEN 128 THEN 'Motive Studio'
        WHEN 129 THEN 'Rockstar Games'
        WHEN 130 THEN 'Rockstar Games'
        WHEN 131 THEN 'Rockstar Games'
        WHEN 132 THEN 'Rockstar Games'
        WHEN 133 THEN 'Rockstar Games'
        WHEN 134 THEN 'Rockstar Games'
        WHEN 135 THEN 'Rockstar Games'
        WHEN 136 THEN 'Rockstar Games'
        WHEN 137 THEN 'Rockstar Games'
        WHEN 138 THEN 'Rockstar Games'
        WHEN 139 THEN 'Rockstar Games'
        WHEN 140 THEN 'Rockstar Games'
        WHEN 141 THEN 'Rockstar Vancouver'
        WHEN 142 THEN 'Team Bondi'
        WHEN 143 THEN 'Rockstar Games'
        WHEN 144 THEN 'Rockstar Games'
        WHEN 145 THEN 'Rockstar Games'
        WHEN 146 THEN 'Remedy Entertainment'
        WHEN 147 THEN 'Remedy Entertainment'
        WHEN 148 THEN 'Remedy Entertainment'
        WHEN 149 THEN 'Remedy Entertainment'
        WHEN 150 THEN 'EA Redwood Shores'
        WHEN 151 THEN 'EA Redwood Shores'
        WHEN 152 THEN 'EA Redwood Shores'
        WHEN 153 THEN 'EA Redwood Shores'
        WHEN 154 THEN 'Capcom'
        WHEN 155 THEN 'Capcom'
        WHEN 156 THEN 'Capcom'
        WHEN 157 THEN 'Capcom'
        WHEN 158 THEN 'Capcom'
        WHEN 159 THEN 'Capcom'
        WHEN 160 THEN 'Capcom'
        WHEN 161 THEN 'Capcom'
        WHEN 162 THEN 'Capcom'
        WHEN 163 THEN 'Capcom'
        WHEN 164 THEN 'Capcom'
        WHEN 165 THEN 'Capcom'
        WHEN 166 THEN 'Capcom'
        WHEN 167 THEN 'Capcom'
        WHEN 168 THEN 'Capcom'
        WHEN 169 THEN 'Capcom'
        WHEN 170 THEN 'Capcom'
        WHEN 171 THEN 'Capcom'
        WHEN 172 THEN 'Capcom'
        WHEN 173 THEN 'Capcom'
        WHEN 174 THEN 'Capcom'
        WHEN 175 THEN 'Capcom'
        WHEN 176 THEN 'Capcom'
        WHEN 177 THEN 'Capcom'
        WHEN 178 THEN 'Capcom'
        WHEN 179 THEN 'Capcom'
        WHEN 180 THEN 'Capcom'
        WHEN 181 THEN 'Capcom'
        WHEN 182 THEN 'Capcom'
        WHEN 183 THEN 'Capcom'
        WHEN 184 THEN 'Capcom'
        WHEN 185 THEN 'Capcom'
        WHEN 186 THEN 'Capcom'
        WHEN 187 THEN 'Capcom'
        WHEN 188 THEN 'Capcom'
        WHEN 189 THEN 'Capcom'
        WHEN 190 THEN 'Bandai Namco Studios'
        WHEN 191 THEN 'Bandai Namco Studios'
        WHEN 192 THEN 'Bandai Namco Studios'
        WHEN 193 THEN 'Bandai Namco Studios'
        WHEN 194 THEN 'Bandai Namco Studios'
        WHEN 195 THEN 'Bandai Namco Studios'
        WHEN 196 THEN 'Bandai Namco Studios'
        WHEN 197 THEN 'Bandai Namco Studios'
        WHEN 198 THEN 'NetherRealm Studios'
        WHEN 199 THEN 'NetherRealm Studios'
        WHEN 200 THEN 'NetherRealm Studios'
        WHEN 201 THEN 'NetherRealm Studios'
        WHEN 202 THEN 'NetherRealm Studios'
        WHEN 203 THEN 'NetherRealm Studios'
        WHEN 204 THEN 'NetherRealm Studios'
        WHEN 205 THEN 'NetherRealm Studios'
        WHEN 206 THEN 'NetherRealm Studios'
        WHEN 207 THEN 'NetherRealm Studios'
        WHEN 208 THEN 'NetherRealm Studios'
        WHEN 209 THEN 'Arc System Works'
        WHEN 210 THEN 'Arc System Works'
        WHEN 211 THEN 'Arc System Works'
        WHEN 212 THEN 'Arc System Works'
        WHEN 213 THEN 'Arc System Works'
        WHEN 214 THEN 'Arc System Works'
        WHEN 215 THEN 'Arc System Works'
        WHEN 216 THEN 'Arc System Works'
        WHEN 217 THEN 'SNK'
        WHEN 218 THEN 'SNK'
        WHEN 219 THEN 'SNK'
        WHEN 220 THEN 'Bandai Namco Studios'
        WHEN 221 THEN 'Bandai Namco Studios'
        WHEN 222 THEN 'Bandai Namco Studios'
        WHEN 223 THEN 'Bandai Namco Studios'
        WHEN 224 THEN 'Bandai Namco Studios'
        WHEN 225 THEN 'Bandai Namco Studios'
        WHEN 226 THEN 'Team Ninja'
        WHEN 227 THEN 'Team Ninja'
        WHEN 228 THEN 'Sega AM2'
        WHEN 229 THEN 'Iron Galaxy'
        WHEN 230 THEN 'SNK'
        WHEN 231 THEN 'Cygames'
        WHEN 232 THEN 'Cygames'
        WHEN 233 THEN 'Capcom'
        WHEN 234 THEN 'Capcom'
        WHEN 235 THEN 'Capcom'
        WHEN 236 THEN 'Capcom'
        WHEN 237 THEN 'CyberConnect2'
        WHEN 238 THEN 'CyberConnect2'
        WHEN 239 THEN 'CyberConnect2'
        WHEN 240 THEN 'CyberConnect2'
        WHEN 241 THEN 'CyberConnect2'
        WHEN 242 THEN 'CyberConnect2'
        WHEN 243 THEN 'CyberConnect2'
        WHEN 244 THEN 'CyberConnect2'
        WHEN 245 THEN 'CyberConnect2'
        WHEN 246 THEN 'Omega Force'
        WHEN 247 THEN 'Omega Force'
        WHEN 248 THEN 'Byking'
        WHEN 249 THEN 'Byking'
        WHEN 250 THEN 'CyberConnect2'
        WHEN 251 THEN 'Atlus'
        WHEN 252 THEN 'Atlus'
        WHEN 253 THEN 'Atlus'
        WHEN 254 THEN 'Atlus'
        WHEN 255 THEN 'Atlus'
        WHEN 256 THEN 'Atlus'
        WHEN 257 THEN 'Atlus'
        WHEN 258 THEN 'Atlus'
        WHEN 259 THEN 'Square Enix'
        WHEN 260 THEN 'Square Enix'
        WHEN 261 THEN 'Square Enix'
        WHEN 262 THEN 'Square Enix'
        WHEN 263 THEN 'Square Enix'
        WHEN 264 THEN 'Square Enix'
        WHEN 265 THEN 'Square Enix'
        WHEN 266 THEN 'Square Enix'
        WHEN 267 THEN 'Square Enix'
        WHEN 268 THEN 'Square Enix'
        WHEN 269 THEN 'Square Enix'
        WHEN 270 THEN 'Square Enix'
        WHEN 271 THEN 'Square Enix'
        WHEN 272 THEN 'Square Enix'
        WHEN 273 THEN 'Square Enix'
        WHEN 274 THEN 'Square Enix'
        WHEN 275 THEN 'Square Enix'
        WHEN 276 THEN 'Square Enix'
        WHEN 277 THEN 'Square Enix'
        WHEN 278 THEN 'Square Enix'
        WHEN 279 THEN 'Square Enix'
        WHEN 280 THEN 'Square Enix'
        WHEN 281 THEN 'Square Enix'
        WHEN 282 THEN 'Square Enix'
        WHEN 283 THEN 'Square Enix'
        WHEN 284 THEN 'Square Enix'
        WHEN 285 THEN 'Square Enix'
        WHEN 286 THEN 'Square Enix'
        WHEN 287 THEN 'Square Enix'
        WHEN 288 THEN 'Square Enix'
        WHEN 289 THEN 'Square Enix'
        WHEN 290 THEN 'Square Enix'
        WHEN 291 THEN 'Square Enix'
        WHEN 292 THEN 'Square Enix'
        WHEN 293 THEN 'Square Enix'
        WHEN 294 THEN 'Square Enix'
        WHEN 295 THEN 'Square Enix'
        WHEN 296 THEN 'Square Enix'
        WHEN 297 THEN 'Square Enix'
        WHEN 298 THEN 'Square Enix'
        WHEN 299 THEN 'Square Enix'
        WHEN 300 THEN 'Square Enix'
        WHEN 301 THEN 'Ryu Ga Gotoku Studio'
        WHEN 302 THEN 'Ryu Ga Gotoku Studio'
        WHEN 303 THEN 'Ryu Ga Gotoku Studio'
        WHEN 304 THEN 'Ryu Ga Gotoku Studio'
        WHEN 305 THEN 'Ryu Ga Gotoku Studio'
        WHEN 306 THEN 'Ryu Ga Gotoku Studio'
        WHEN 307 THEN 'Ryu Ga Gotoku Studio'
        WHEN 308 THEN 'Ryu Ga Gotoku Studio'
        WHEN 309 THEN 'Ryu Ga Gotoku Studio'
        WHEN 310 THEN 'Ryu Ga Gotoku Studio'
        WHEN 311 THEN 'Ryu Ga Gotoku Studio'
        WHEN 312 THEN 'Ryu Ga Gotoku Studio'
        WHEN 313 THEN 'Atlus'
        WHEN 314 THEN 'Bandai Namco Studios'
        WHEN 315 THEN 'Bandai Namco Studios'
        WHEN 316 THEN 'Bandai Namco Studios'
        WHEN 317 THEN 'Bandai Namco Studios'
        WHEN 318 THEN 'Bandai Namco Studios'
        WHEN 319 THEN 'Nihon Falcom'
        WHEN 320 THEN 'Nihon Falcom'
        WHEN 321 THEN 'Nihon Falcom'
        WHEN 322 THEN 'Nihon Falcom'
        WHEN 323 THEN 'Nihon Falcom'
        WHEN 324 THEN 'Nihon Falcom'
        WHEN 325 THEN 'Nihon Falcom'
        WHEN 326 THEN 'Nihon Falcom'
        WHEN 327 THEN 'Nihon Falcom'
        WHEN 328 THEN 'Atlus'
        WHEN 329 THEN 'FromSoftware'
        WHEN 330 THEN 'FromSoftware'
        WHEN 331 THEN 'FromSoftware'
        WHEN 332 THEN 'FromSoftware'
        WHEN 333 THEN 'FromSoftware'
        WHEN 334 THEN 'FromSoftware'
        WHEN 335 THEN 'FromSoftware'
        WHEN 336 THEN 'FromSoftware'
        WHEN 337 THEN 'FromSoftware'
        WHEN 338 THEN 'Round8 Studio'
        WHEN 339 THEN 'Hexworks'
        WHEN 340 THEN 'Hexworks'
        WHEN 341 THEN 'Team Ninja'
        WHEN 342 THEN 'Team Ninja'
        WHEN 343 THEN 'Team Ninja'
        WHEN 344 THEN 'Team Ninja'
        WHEN 345 THEN 'Shift'
        WHEN 346 THEN 'Gunfire Games'
        WHEN 347 THEN 'Gunfire Games'
        WHEN 348 THEN 'Cold Symmetry'
        WHEN 349 THEN 'Deck13'
        WHEN 350 THEN 'Deck13'
        WHEN 351 THEN 'A44 Games'
        WHEN 352 THEN 'Ska Studios'
        WHEN 353 THEN 'Ska Studios'
        WHEN 354 THEN 'Supergiant Games'
        WHEN 355 THEN 'Supergiant Games'
        WHEN 356 THEN 'Motion Twin'
        WHEN 357 THEN 'Team Cherry'
        WHEN 358 THEN 'Team Cherry'
        WHEN 359 THEN 'Studio MDHR'
        WHEN 360 THEN 'Moon Studios'
        WHEN 361 THEN 'Moon Studios'
        WHEN 362 THEN 'Maddy Makes Games'
        WHEN 363 THEN 'Yacht Club Games'
        WHEN 364 THEN 'Sabotage Studio'
        WHEN 365 THEN 'The Game Kitchen'
        WHEN 366 THEN 'The Game Kitchen'
        WHEN 367 THEN 'Thomas Happ Games'
        WHEN 368 THEN 'Thomas Happ Games'
        WHEN 369 THEN 'Heart Machine'
        WHEN 370 THEN 'Heart Machine'
        WHEN 371 THEN 'Askiisoft'
        WHEN 372 THEN 'Dennaton Games'
        WHEN 373 THEN 'Dennaton Games'
        WHEN 374 THEN 'Dodge Roll'
        WHEN 375 THEN 'Dodge Roll'
        WHEN 376 THEN 'Nicalis'
        WHEN 377 THEN 'Nicalis'
        WHEN 378 THEN 'Hopoo Games'
        WHEN 379 THEN 'Hopoo Games'
        WHEN 380 THEN 'Hopoo Games'
        WHEN 381 THEN 'Cellar Door Games'
        WHEN 382 THEN 'Cellar Door Games'
        WHEN 383 THEN 'poncle'
        WHEN 384 THEN 'Blobfish'
        WHEN 385 THEN 'Chasing Carrots'
        WHEN 386 THEN 'LocalThunk'
        WHEN 387 THEN 'Mega Crit'
        WHEN 388 THEN 'Daniel Mullins Games'
        WHEN 389 THEN 'Four Quarters'
        WHEN 390 THEN 'Red Hook Studios'
        WHEN 391 THEN 'Red Hook Studios'
        WHEN 392 THEN 'Subset Games'
        WHEN 393 THEN 'Subset Games'
        WHEN 394 THEN 'Lucas Pope'
        WHEN 395 THEN 'Lucas Pope'
        WHEN 396 THEN 'Toby Fox'
        WHEN 397 THEN 'Toby Fox'
        WHEN 398 THEN 'ZA/UM'
        WHEN 399 THEN 'Cardboard Computer'
        WHEN 400 THEN 'Mobius Digital'
        WHEN 401 THEN 'Galactic Cafe'
        WHEN 402 THEN 'Galactic Cafe'
        WHEN 403 THEN 'Giant Sparrow'
        WHEN 404 THEN 'Campo Santo'
        WHEN 405 THEN 'Fullbright'
        WHEN 406 THEN 'Night School Studio'
        WHEN 407 THEN 'Night School Studio'
        WHEN 408 THEN 'Infinite Fall'
        WHEN 409 THEN 'Dontnod Entertainment'
        WHEN 410 THEN 'Dontnod Entertainment'
        WHEN 411 THEN 'Dontnod Entertainment'
        WHEN 412 THEN 'Dontnod Entertainment'
        WHEN 413 THEN 'Dontnod Entertainment'
        WHEN 414 THEN 'Dontnod Entertainment'
        WHEN 415 THEN 'DigixArt'
        WHEN 416 THEN 'Quantic Dream'
        WHEN 417 THEN 'Quantic Dream'
        WHEN 418 THEN 'Quantic Dream'
        WHEN 419 THEN 'Supermassive Games'
        WHEN 420 THEN 'Supermassive Games'
        WHEN 421 THEN 'Telltale Games'
        WHEN 422 THEN 'Telltale Games'
        WHEN 423 THEN 'Telltale Games'
        WHEN 424 THEN 'Telltale Games'
        WHEN 425 THEN 'Telltale Games'
        WHEN 426 THEN 'Telltale Games'
        WHEN 427 THEN 'Telltale Games'
        WHEN 428 THEN 'Mojang Studios'
        WHEN 429 THEN 'Mojang Studios'
        WHEN 430 THEN 'Gearbox Software'
        WHEN 431 THEN 'Gearbox Software'
        WHEN 432 THEN 'Gearbox Software'
        WHEN 433 THEN 'Gearbox Software'
        WHEN 434 THEN 'Gearbox Software'
        WHEN 435 THEN 'Gearbox Software'
        WHEN 436 THEN '2K Boston'
        WHEN 437 THEN '2K Boston'
        WHEN 438 THEN '2K Boston'
        WHEN 439 THEN 'Looking Glass Studios'
        WHEN 440 THEN 'Looking Glass Studios'
        WHEN 441 THEN 'Arkane Studios'
        WHEN 442 THEN 'Arkane Studios'
        WHEN 443 THEN 'Arkane Studios'
        WHEN 444 THEN 'Arkane Studios'
        WHEN 445 THEN 'Arkane Studios'
        WHEN 446 THEN 'id Software'
        WHEN 447 THEN 'id Software'
        WHEN 448 THEN 'id Software'
        WHEN 449 THEN 'id Software'
        WHEN 450 THEN 'id Software'
        WHEN 451 THEN 'id Software'
        WHEN 452 THEN 'id Software'
        WHEN 453 THEN 'id Software'
        WHEN 454 THEN 'id Software'
        WHEN 455 THEN 'id Software'
        WHEN 456 THEN 'id Software'
        WHEN 457 THEN 'id Software'
        WHEN 458 THEN 'id Software'
        WHEN 459 THEN 'id Software'
        WHEN 460 THEN 'id Software'
        WHEN 461 THEN 'Crytek'
        WHEN 462 THEN 'Crytek'
        WHEN 463 THEN 'Crytek'
        WHEN 464 THEN 'Crytek'
        WHEN 465 THEN 'Crytek'
        WHEN 466 THEN 'Crytek'
        WHEN 467 THEN 'Ubisoft'
        WHEN 468 THEN 'Ubisoft'
        WHEN 469 THEN 'Ubisoft'
        WHEN 470 THEN 'Ubisoft'
        WHEN 471 THEN 'Ubisoft'
        WHEN 472 THEN 'Ubisoft'
        WHEN 473 THEN 'Ubisoft'
        WHEN 474 THEN 'Ubisoft'
        WHEN 475 THEN 'Ubisoft'
        WHEN 476 THEN 'Ubisoft'
        WHEN 477 THEN 'Ubisoft'
        WHEN 478 THEN 'Ubisoft'
        WHEN 479 THEN 'Ubisoft'
        WHEN 480 THEN 'Ubisoft'
        WHEN 481 THEN 'Ubisoft'
        WHEN 482 THEN 'Ubisoft'
        WHEN 483 THEN 'Ubisoft'
        WHEN 484 THEN 'Ubisoft'
        WHEN 485 THEN 'Ubisoft'
        WHEN 486 THEN 'Ubisoft'
        WHEN 487 THEN 'Ubisoft'
        WHEN 488 THEN 'Ubisoft'
        WHEN 489 THEN 'Ubisoft'
        WHEN 490 THEN 'Ubisoft'
        WHEN 491 THEN 'Ubisoft'
        WHEN 492 THEN 'Ubisoft'
        WHEN 493 THEN 'Ubisoft'
        WHEN 494 THEN 'Ubisoft'
        WHEN 495 THEN 'Ubisoft'
        WHEN 496 THEN 'Ubisoft Montreal'
        WHEN 497 THEN 'Ubisoft Montreal'
        WHEN 498 THEN 'Ubisoft Montreal'
        WHEN 499 THEN 'Ubisoft Montreal'
        WHEN 500 THEN 'Ubisoft Montreal'
        WHEN 501 THEN 'Ubisoft Montreal'
        WHEN 502 THEN 'Ubisoft Montreal'
        WHEN 503 THEN 'Ubisoft Montreal'
        WHEN 504 THEN 'Ubisoft Montreal'
        WHEN 505 THEN 'Ubisoft Montreal'
        WHEN 506 THEN 'Ubisoft Montreal'
        WHEN 507 THEN 'Ubisoft Montreal'
        WHEN 508 THEN 'Ubisoft Montreal'
        WHEN 509 THEN 'Ubisoft Montreal'
        WHEN 510 THEN 'Ubisoft Montreal'
        WHEN 511 THEN 'Ubisoft Montreal'
        WHEN 512 THEN 'Ubisoft Montreal'
        WHEN 513 THEN 'Ubisoft Montreal'
        WHEN 514 THEN 'Ubisoft Montreal'
        WHEN 515 THEN 'Ubisoft'
        WHEN 516 THEN 'Ubisoft'
        WHEN 517 THEN 'Ubisoft'
        WHEN 518 THEN 'Ubisoft'
        WHEN 519 THEN 'Ubisoft'
        WHEN 520 THEN 'Ubisoft'
        WHEN 521 THEN 'Ubisoft'
        WHEN 522 THEN 'Ubisoft'
        WHEN 523 THEN 'Ubisoft'
        WHEN 524 THEN 'Ubisoft'
        WHEN 525 THEN 'Ubisoft'
        WHEN 526 THEN 'Ubisoft'
        WHEN 527 THEN 'Playground Games'
        WHEN 528 THEN 'Playground Games'
        WHEN 529 THEN 'Playground Games'
        WHEN 530 THEN 'Playground Games'
        WHEN 531 THEN 'Playground Games'
        WHEN 532 THEN 'Playground Games'
        WHEN 533 THEN 'Playground Games'
        WHEN 534 THEN '343 Industries'
        WHEN 535 THEN '343 Industries'
        WHEN 536 THEN '343 Industries'
        WHEN 537 THEN '343 Industries'
        WHEN 538 THEN '343 Industries'
        WHEN 539 THEN '343 Industries'
        WHEN 540 THEN '343 Industries'
        WHEN 541 THEN '343 Industries'
        WHEN 542 THEN '343 Industries'
        WHEN 543 THEN '343 Industries'
        WHEN 544 THEN 'The Coalition'
        WHEN 545 THEN 'The Coalition'
        WHEN 546 THEN 'The Coalition'
        WHEN 547 THEN 'The Coalition'
        WHEN 548 THEN 'The Coalition'
        WHEN 549 THEN 'The Coalition'
        WHEN 550 THEN 'Asobo Studio'
        WHEN 551 THEN 'Asobo Studio'
        WHEN 552 THEN 'Worlds Edge'
        WHEN 553 THEN 'Undead Labs'
        WHEN 554 THEN 'Undead Labs'
        WHEN 555 THEN 'Ninja Theory'
        WHEN 556 THEN 'Tango Gameworks'
        WHEN 557 THEN 'Obsidian Entertainment'
        WHEN 558 THEN 'Double Fine Productions'
        WHEN 559 THEN 'Double Fine Productions'
        WHEN 560 THEN 'Insomniac Games'
        WHEN 561 THEN 'Rare'
        WHEN 562 THEN 'Moon Studios'
        WHEN 563 THEN 'The Coalition'
        WHEN 564 THEN 'Mojang Studios'
        WHEN 565 THEN 'Mojang Studios'
        WHEN 566 THEN 'Microsoft Casual Games'
        WHEN 567 THEN 'inXile Entertainment'
        WHEN 568 THEN 'inXile Entertainment'
        WHEN 569 THEN 'inXile Entertainment'
        WHEN 570 THEN 'Obsidian Entertainment'
        WHEN 571 THEN 'Obsidian Entertainment'
        WHEN 572 THEN 'Obsidian Entertainment'
        WHEN 573 THEN 'Owlcat Games'
        WHEN 574 THEN 'Owlcat Games'
        WHEN 575 THEN 'Owlcat Games'
        WHEN 576 THEN 'Relic Entertainment'
        WHEN 577 THEN 'Relic Entertainment'
        WHEN 578 THEN 'Fatshark'
        WHEN 579 THEN 'Fatshark'
        WHEN 580 THEN 'Fatshark'
        WHEN 581 THEN 'Creative Assembly'
        WHEN 582 THEN 'Creative Assembly'
        WHEN 583 THEN 'Creative Assembly'
        WHEN 584 THEN 'Creative Assembly'
        WHEN 585 THEN 'Creative Assembly'
        WHEN 586 THEN 'Creative Assembly'
        WHEN 587 THEN 'Creative Assembly'
        WHEN 588 THEN 'Creative Assembly'
        WHEN 589 THEN 'Creative Assembly'
        WHEN 590 THEN 'Creative Assembly'
        WHEN 591 THEN 'Creative Assembly'
        WHEN 592 THEN 'Firaxis Games'
        WHEN 593 THEN 'Firaxis Games'
        WHEN 594 THEN 'Firaxis Games'
        WHEN 595 THEN 'Firaxis Games'
        WHEN 596 THEN 'Firaxis Games'
        WHEN 597 THEN 'Firaxis Games'
        WHEN 598 THEN 'Firaxis Games'
        WHEN 599 THEN 'Firaxis Games'
        WHEN 600 THEN 'Firaxis Games'
        WHEN 601 THEN 'Firaxis Games'
        WHEN 602 THEN 'Firaxis Games'
        WHEN 603 THEN 'Maxis'
        WHEN 604 THEN 'Maxis'
        WHEN 605 THEN 'Maxis'
        WHEN 606 THEN 'Maxis'
        WHEN 607 THEN 'Maxis'
        WHEN 608 THEN 'Maxis'
        WHEN 609 THEN 'Maxis'
        WHEN 610 THEN 'Maxis'
        WHEN 611 THEN 'Colossal Order'
        WHEN 612 THEN 'Colossal Order'
        WHEN 613 THEN 'Monte Cristo'
        WHEN 614 THEN 'Frontier Developments'
        WHEN 615 THEN 'Frontier Developments'
        WHEN 616 THEN 'Frontier Developments'
        WHEN 617 THEN 'Frontier Developments'
        WHEN 618 THEN 'Frontier Developments'
        WHEN 619 THEN 'Frontier Developments'
        WHEN 620 THEN 'Chris Sawyer Productions'
        WHEN 621 THEN 'Chris Sawyer Productions'
        WHEN 622 THEN 'Frontier Developments'
        WHEN 623 THEN 'Two Point Studios'
        WHEN 624 THEN 'Two Point Studios'
        WHEN 625 THEN 'GIANTS Software'
        WHEN 626 THEN 'GIANTS Software'
        WHEN 627 THEN 'GIANTS Software'
        WHEN 628 THEN 'GIANTS Software'
        WHEN 629 THEN 'SCS Software'
        WHEN 630 THEN 'Dovetail Games'
        WHEN 631 THEN 'Dovetail Games'
        WHEN 632 THEN 'Dovetail Games'
        WHEN 633 THEN 'Dovetail Games'
        WHEN 634 THEN 'Dovetail Games'
        WHEN 635 THEN 'Dovetail Games'
        WHEN 636 THEN 'Altfuture'
        WHEN 637 THEN 'Saber Interactive'
        WHEN 638 THEN 'Saber Interactive'
        WHEN 639 THEN 'Saber Interactive'
        WHEN 640 THEN 'BeamNG'
        WHEN 641 THEN 'CarX Technologies'
        WHEN 642 THEN 'Kunos Simulazioni'
        WHEN 643 THEN 'Kunos Simulazioni'
        WHEN 644 THEN 'Reiza Studios'
        WHEN 645 THEN 'Reiza Studios'
        WHEN 646 THEN 'iRacing.com Motorsport Simulations'
        WHEN 647 THEN 'Image Space Incorporated'
        WHEN 648 THEN 'Image Space Incorporated'
        WHEN 649 THEN 'Slightly Mad Studios'
        WHEN 650 THEN 'Slightly Mad Studios'
        WHEN 651 THEN 'Slightly Mad Studios'
        WHEN 652 THEN 'Codemasters'
        WHEN 653 THEN 'Codemasters'
        WHEN 654 THEN 'Codemasters'
        WHEN 655 THEN 'Codemasters'
        WHEN 656 THEN 'Codemasters'
        WHEN 657 THEN 'Kylotonn'
        WHEN 658 THEN 'Kylotonn'
        WHEN 659 THEN 'Kylotonn'
        WHEN 660 THEN 'Kylotonn'
        WHEN 661 THEN 'Codemasters'
        WHEN 662 THEN 'Codemasters'
        WHEN 663 THEN 'Codemasters'
        WHEN 664 THEN 'Codemasters'
        WHEN 665 THEN 'Codemasters'
        WHEN 666 THEN 'Codemasters'
        WHEN 667 THEN 'Codemasters'
        WHEN 668 THEN 'Codemasters'
        WHEN 669 THEN 'Codemasters'
        WHEN 670 THEN 'Milestone'
        WHEN 671 THEN 'Milestone'
        WHEN 672 THEN 'Milestone'
        WHEN 673 THEN 'Milestone'
        WHEN 674 THEN 'Milestone'
        WHEN 675 THEN 'Milestone'
        WHEN 676 THEN 'Visual Concepts'
        WHEN 677 THEN 'Visual Concepts'
        WHEN 678 THEN 'Visual Concepts'
        WHEN 679 THEN 'Visual Concepts'
        WHEN 680 THEN 'Visual Concepts'
        WHEN 681 THEN 'Visual Concepts'
        WHEN 682 THEN 'Visual Concepts'
        WHEN 683 THEN 'Visual Concepts'
        WHEN 684 THEN 'Visual Concepts'
        WHEN 685 THEN 'Visual Concepts'
        WHEN 686 THEN 'Visual Concepts'
        WHEN 687 THEN 'Visual Concepts'
        WHEN 688 THEN 'Visual Concepts'
        WHEN 689 THEN 'EA Vancouver'
        WHEN 690 THEN 'EA Vancouver'
        WHEN 691 THEN 'EA Vancouver'
        WHEN 692 THEN 'EA Vancouver'
        WHEN 693 THEN 'EA Vancouver'
        WHEN 694 THEN 'EA Vancouver'
        WHEN 695 THEN 'EA Vancouver'
        WHEN 696 THEN 'EA Vancouver'
        WHEN 697 THEN 'Konami Digital Entertainment'
        WHEN 698 THEN 'Konami Digital Entertainment'
        WHEN 699 THEN 'Konami Digital Entertainment'
        WHEN 700 THEN 'EA Vancouver'
        WHEN 701 THEN 'EA Vancouver'
        WHEN 702 THEN 'EA Vancouver'
        WHEN 703 THEN 'EA Vancouver'
        WHEN 704 THEN 'EA Vancouver'
        WHEN 705 THEN 'EA Vancouver'
        WHEN 706 THEN 'EA Vancouver'
        WHEN 707 THEN 'EA Vancouver'
        WHEN 708 THEN 'San Diego Studio'
        WHEN 709 THEN 'San Diego Studio'
        WHEN 710 THEN 'San Diego Studio'
        WHEN 711 THEN 'San Diego Studio'
        WHEN 712 THEN 'Vicarious Visions'
        WHEN 713 THEN 'Vicarious Visions'
        WHEN 714 THEN 'EA Black Box'
        WHEN 715 THEN 'EA Black Box'
        WHEN 716 THEN 'EA Black Box'
        WHEN 717 THEN 'EA Black Box'
        WHEN 718 THEN 'EA Canada'
        WHEN 719 THEN 'EA Canada'
        WHEN 720 THEN 'Criterion Games'
        WHEN 721 THEN 'EA Black Box'
        WHEN 722 THEN 'EA Black Box'
        WHEN 723 THEN 'EA Black Box'
        WHEN 724 THEN 'EA Black Box'
        WHEN 725 THEN 'EA Black Box'
        WHEN 726 THEN 'EA Black Box'
        WHEN 727 THEN 'EA Black Box'
        WHEN 728 THEN 'EA Black Box'
        WHEN 729 THEN 'EA Black Box'
        WHEN 730 THEN 'EA Black Box'
        WHEN 731 THEN 'EA Black Box'
        WHEN 732 THEN 'EA Black Box'
        WHEN 733 THEN 'Rockstar San Diego'
        WHEN 734 THEN 'Rockstar San Diego'
        WHEN 735 THEN 'Rockstar San Diego'
        WHEN 736 THEN 'Rockstar Games'
        WHEN 737 THEN 'Insomniac Games'
        WHEN 738 THEN 'Insomniac Games'
        WHEN 739 THEN 'Insomniac Games'
        WHEN 740 THEN 'Insomniac Games'
        WHEN 741 THEN 'Insomniac Games'
        WHEN 742 THEN 'Eidos-Montréal'
        WHEN 743 THEN 'Crystal Dynamics'
        WHEN 744 THEN 'Raven Software'
        WHEN 745 THEN 'Raven Software'
        WHEN 746 THEN 'Raven Software'
        WHEN 747 THEN 'Raven Software'
        WHEN 748 THEN 'Raven Software'
        WHEN 749 THEN 'High Moon Studios'
        WHEN 750 THEN 'Firaxis Games'
        WHEN 751 THEN 'Rocksteady Studios'
        WHEN 752 THEN 'Rocksteady Studios'
        WHEN 753 THEN 'Rocksteady Studios'
        WHEN 754 THEN 'Rocksteady Studios'
        WHEN 755 THEN 'Rocksteady Studios'
        WHEN 756 THEN 'Monolith Productions'
        WHEN 757 THEN 'Monolith Productions'
        WHEN 758 THEN 'Avalanche Studios'
        WHEN 759 THEN 'Shiny Entertainment'
        WHEN 760 THEN 'Traveller.s Tales'
        WHEN 761 THEN 'Traveller.s Tales'
        WHEN 762 THEN 'Traveller.s Tales'
        WHEN 763 THEN 'Traveller.s Tales'
        WHEN 764 THEN 'Traveller.s Tales'
        WHEN 765 THEN 'Traveller.s Tales'
        WHEN 766 THEN 'Traveller.s Tales'
        WHEN 767 THEN 'Traveller.s Tales'
        WHEN 768 THEN 'Traveller.s Tales'
        WHEN 769 THEN 'Traveller.s Tales'
        WHEN 770 THEN 'Traveller.s Tales'
        WHEN 771 THEN 'Traveller.s Tales'
        WHEN 772 THEN 'Traveller.s Tales'
        WHEN 773 THEN 'Traveller.s Tales'
        WHEN 774 THEN 'Traveller.s Tales'
        WHEN 775 THEN 'Traveller.s Tales'
        WHEN 776 THEN 'Traveller.s Tales'
        WHEN 777 THEN 'Traveller.s Tales'
        WHEN 778 THEN 'Avalanche Software'
        WHEN 779 THEN 'Unbroken Studios'
        WHEN 780 THEN 'Crystal Dynamics'
        WHEN 781 THEN 'Crystal Dynamics'
        WHEN 782 THEN 'Crystal Dynamics'
        WHEN 783 THEN 'Crystal Dynamics'
        WHEN 784 THEN 'Crystal Dynamics'
        WHEN 785 THEN 'Crystal Dynamics'
        WHEN 786 THEN 'Crystal Dynamics'
        WHEN 787 THEN 'Naughty Dog'
        WHEN 788 THEN 'Naughty Dog'
        WHEN 789 THEN 'Naughty Dog'
        WHEN 790 THEN 'Naughty Dog'
        WHEN 791 THEN 'Naughty Dog'
        WHEN 792 THEN 'Bend Studio'
        WHEN 793 THEN 'Guerrilla Games'
        WHEN 794 THEN 'Guerrilla Games'
        WHEN 795 THEN 'Sucker Punch Productions'
        WHEN 796 THEN 'Santa Monica Studio'
        WHEN 797 THEN 'Santa Monica Studio'
        WHEN 798 THEN 'Insomniac Games'
        WHEN 799 THEN 'Housemarque'
        WHEN 800 THEN 'Arrowhead Game Studios'
        WHEN 801 THEN 'Arrowhead Game Studios'
        WHEN 802 THEN 'Kojima Productions'
        WHEN 803 THEN 'Kojima Productions'
        WHEN 804 THEN 'Team Ico'
        WHEN 805 THEN 'GenDesign'
        WHEN 806 THEN 'Sucker Punch Productions'
        WHEN 807 THEN 'Sucker Punch Productions'
        WHEN 808 THEN 'Sumo Digital'
        WHEN 809 THEN 'Sumo Digital'
        WHEN 810 THEN 'Media Molecule'
        WHEN 811 THEN 'Polyphony Digital'
        WHEN 812 THEN 'Polyphony Digital'
        WHEN 813 THEN 'Polyphony Digital'
        WHEN 814 THEN 'Polyphony Digital'
        WHEN 815 THEN 'Polyphony Digital'
        WHEN 816 THEN 'Polyphony Digital'
        WHEN 817 THEN 'Polyphony Digital'
        WHEN 818 THEN 'Santa Monica Studio'
        WHEN 819 THEN 'Guerrilla Games'
        WHEN 820 THEN 'Guerrilla Games'
        WHEN 821 THEN 'Guerrilla Games'
        WHEN 822 THEN 'Insomniac Games'
        WHEN 823 THEN 'Insomniac Games'
        WHEN 824 THEN 'Insomniac Games'
        WHEN 825 THEN 'Japan Studio'
        WHEN 826 THEN 'Japan Studio'
        WHEN 827 THEN 'Ready at Dawn'
        WHEN 828 THEN 'Sumo Digital'
        WHEN 829 THEN 'Team Asobi'
        WHEN 830 THEN 'Team Asobi'
        WHEN 831 THEN 'Nintendo'
        WHEN 832 THEN 'Nintendo'
        WHEN 833 THEN 'Nintendo'
        WHEN 834 THEN 'Nintendo'
        WHEN 835 THEN 'Nintendo'
        WHEN 836 THEN 'Nintendo'
        WHEN 837 THEN 'Nintendo'
        WHEN 838 THEN 'Nintendo'
        WHEN 839 THEN 'Nintendo'
        WHEN 840 THEN 'Nintendo'
        WHEN 841 THEN 'Nintendo'
        WHEN 842 THEN 'Nintendo'
        WHEN 843 THEN 'Nintendo'
        WHEN 844 THEN 'Nintendo'
        WHEN 845 THEN 'Nintendo'
        WHEN 846 THEN 'Nintendo'
        WHEN 847 THEN 'Nintendo'
        WHEN 848 THEN 'Nintendo'
        WHEN 849 THEN 'Nintendo'
        WHEN 850 THEN 'Nintendo EAD'
        WHEN 851 THEN 'Nintendo'
        WHEN 852 THEN 'Nintendo'
        WHEN 853 THEN 'Nintendo'
        WHEN 854 THEN 'Nintendo'
        WHEN 855 THEN 'Nintendo'
        WHEN 856 THEN 'Nintendo'
        WHEN 857 THEN 'Nintendo'
        WHEN 858 THEN 'Nintendo'
        WHEN 859 THEN 'Nintendo'
        WHEN 860 THEN 'Nintendo R&D1'
        WHEN 861 THEN 'Nintendo'
        WHEN 862 THEN 'Nintendo'
        WHEN 863 THEN 'Nintendo'
        WHEN 864 THEN 'Nintendo'
        WHEN 865 THEN 'Nintendo'
        WHEN 866 THEN 'Nintendo'
        WHEN 867 THEN 'Nintendo'
        WHEN 868 THEN 'Nintendo'
        WHEN 869 THEN 'Nintendo'
        WHEN 870 THEN 'Nintendo'
        WHEN 871 THEN 'Nintendo'
        WHEN 872 THEN 'Nintendo'
        WHEN 873 THEN 'Nintendo'
        WHEN 874 THEN 'Nintendo'
        WHEN 875 THEN 'Nintendo'
        WHEN 876 THEN 'Nintendo'
        WHEN 877 THEN 'Nintendo'
        WHEN 878 THEN 'Nintendo'
        WHEN 879 THEN 'Nintendo'
        WHEN 880 THEN 'Nintendo'
        WHEN 881 THEN 'Nintendo'
        WHEN 882 THEN 'Nintendo'
        WHEN 883 THEN 'Nintendo'
        WHEN 884 THEN 'Nintendo'
        WHEN 885 THEN 'Nintendo'
        WHEN 886 THEN 'Nintendo'
        WHEN 887 THEN 'Nintendo'
        WHEN 888 THEN 'Nintendo'
        WHEN 889 THEN 'Nintendo'
        WHEN 890 THEN 'Nintendo'
        WHEN 891 THEN 'Nintendo'
        WHEN 892 THEN 'Nintendo'
        WHEN 893 THEN 'Nintendo'
        WHEN 894 THEN 'Nintendo'
        WHEN 895 THEN 'Nintendo'
        WHEN 896 THEN 'Nintendo'
        WHEN 897 THEN 'Nintendo'
        WHEN 898 THEN 'Nintendo'
        WHEN 899 THEN 'Nintendo'
        WHEN 900 THEN 'Nintendo'
        WHEN 901 THEN 'Nintendo'
        WHEN 902 THEN 'Nintendo'
        WHEN 903 THEN 'Nintendo'
        WHEN 904 THEN 'Nintendo'
        WHEN 905 THEN 'Nintendo'
        WHEN 906 THEN 'Nintendo'
        WHEN 907 THEN 'Nintendo'
        WHEN 908 THEN 'Nintendo'
        WHEN 909 THEN 'Nintendo'
        WHEN 910 THEN 'Nintendo'
        WHEN 911 THEN 'Nintendo'
        WHEN 912 THEN 'Nintendo'
        WHEN 913 THEN 'Nintendo'
        WHEN 914 THEN 'Nintendo'
        WHEN 915 THEN 'Nintendo'
        WHEN 916 THEN 'Nintendo'
        WHEN 917 THEN 'Nintendo'
        WHEN 918 THEN 'Nintendo'
        WHEN 919 THEN 'Nintendo'
        WHEN 920 THEN 'Marvelous First Studio'
        WHEN 921 THEN 'Capcom'
        WHEN 922 THEN 'Capcom'
        WHEN 923 THEN 'Atlus'
        WHEN 924 THEN 'Atlus'
        WHEN 925 THEN 'Vanillaware'
        WHEN 926 THEN 'Sega'
        WHEN 927 THEN 'Sega'
        WHEN 928 THEN 'Spike Chunsoft'
        WHEN 929 THEN 'Spike Chunsoft'
        WHEN 930 THEN 'Spike Chunsoft'
        WHEN 931 THEN 'Spike Chunsoft'
        WHEN 932 THEN 'Spike Chunsoft'
        WHEN 933 THEN 'Spike Chunsoft'
        WHEN 934 THEN 'Capcom'
        WHEN 935 THEN 'Capcom'
        WHEN 936 THEN 'Capcom'
        WHEN 937 THEN 'Level-5'
        WHEN 938 THEN 'Level-5'
        WHEN 939 THEN 'Level-5'
        WHEN 940 THEN 'Level-5'
        WHEN 941 THEN 'Square Enix'
        WHEN 942 THEN 'PlatinumGames'
        WHEN 943 THEN 'Konami Digital Entertainment'
        WHEN 944 THEN 'Konami Digital Entertainment'
        WHEN 945 THEN 'Konami Digital Entertainment'
        WHEN 946 THEN 'Konami Digital Entertainment'
        WHEN 947 THEN 'Konami Digital Entertainment'
        WHEN 948 THEN 'Konami Digital Entertainment'
        WHEN 949 THEN 'Konami Digital Entertainment'
        WHEN 950 THEN 'Konami Digital Entertainment'
        WHEN 951 THEN 'Team Ninja'
        WHEN 952 THEN 'Team Ninja'
        WHEN 953 THEN 'Team Ninja'
        WHEN 954 THEN 'Team Ninja'
        WHEN 955 THEN 'Gunfire Games'
        WHEN 956 THEN 'Gunfire Games'
        WHEN 957 THEN 'Gunfire Games'
        WHEN 958 THEN 'Gunfire Games'
        WHEN 959 THEN 'Radical Entertainment'
        WHEN 960 THEN 'Radical Entertainment'
        WHEN 961 THEN 'Sucker Punch Productions'
        WHEN 962 THEN 'United Front Games'
        WHEN 963 THEN 'United Front Games'
        WHEN 964 THEN 'Volition'
        WHEN 965 THEN 'Volition'
        WHEN 966 THEN 'Volition'
        WHEN 967 THEN 'Volition'
        WHEN 968 THEN 'Volition'
        WHEN 969 THEN 'Volition'
        WHEN 970 THEN 'Avalanche Studios'
        WHEN 971 THEN 'Avalanche Studios'
        WHEN 972 THEN 'Avalanche Studios'
        WHEN 973 THEN 'Avalanche Studios'
        WHEN 974 THEN 'Hangar 13'
        WHEN 975 THEN 'Hangar 13'
        WHEN 976 THEN 'Hangar 13'
        WHEN 977 THEN 'Hangar 13'
        WHEN 978 THEN 'Hangar 13'
        WHEN 979 THEN 'Hangar 13'
        WHEN 980 THEN 'Spiders'
        WHEN 981 THEN 'Spiders'
        WHEN 982 THEN 'Big Huge Games'
        WHEN 983 THEN 'Big Huge Games'
        WHEN 984 THEN 'Playground Games'
        WHEN 985 THEN 'Playground Games'
        WHEN 986 THEN 'Playground Games'
        WHEN 987 THEN 'Playground Games'
        WHEN 988 THEN 'Playground Games'
        WHEN 989 THEN 'Obsidian Entertainment'
        WHEN 990 THEN 'Obsidian Entertainment'
        WHEN 991 THEN 'Obsidian Entertainment|South Park Digital Studios'
        WHEN 992 THEN 'Obsidian Entertainment|South Park Digital Studios'
        WHEN 993 THEN 'TaleWorlds Entertainment'
        WHEN 994 THEN 'TaleWorlds Entertainment'
        WHEN 995 THEN 'Lo-Fi Games'
        WHEN 996 THEN 'Ludeon Studios'
        WHEN 997 THEN 'Bay 12 Games'
        WHEN 998 THEN 'Wube Software'
        WHEN 999 THEN 'Coffee Stain Studios'
        WHEN 1000 THEN 'Youthcat Studio'
        WHEN 1001 THEN 'Klei Entertainment'
        WHEN 1002 THEN '11 bit studios'
        WHEN 1003 THEN '11 bit studios'
        WHEN 1004 THEN 'Shining Rock Software'
        WHEN 1005 THEN 'Eremite Games'
        WHEN 1006 THEN 'Mechanistry'
        WHEN 1007 THEN 'Slavic Magic'
        WHEN 1008 THEN '3Division'
        WHEN 1009 THEN 'Blue Byte'
        WHEN 1010 THEN 'Blue Byte'
        WHEN 1011 THEN 'Blue Byte'
        WHEN 1012 THEN 'Haemimont Games'
        WHEN 1013 THEN 'Haemimont Games'
        WHEN 1014 THEN 'Haemimont Games'
        WHEN 1015 THEN 'Introversion Software'
        WHEN 1016 THEN 'The Indie Stone'
        WHEN 1017 THEN '11 bit studios'
        WHEN 1018 THEN 'Numantian Games'
        WHEN 1019 THEN 'Haemimont Games'
        WHEN 1020 THEN 'Haemimont Games'
        WHEN 1021 THEN 'Bulwark Studios'
        WHEN 1022 THEN 'Gentlymad Studios'
        WHEN 1023 THEN 'Shiro Games'
        WHEN 1024 THEN 'Toukana Interactive'
        WHEN 1025 THEN 'Dinosaur Polo Club'
        WHEN 1026 THEN 'Dinosaur Polo Club'
        WHEN 1027 THEN 'FuturLab'
        WHEN 1028 THEN 'Frozen District'
        WHEN 1029 THEN 'Frozen District'
        WHEN 1030 THEN 'The Irregular Corporation'
        WHEN 1031 THEN 'The Irregular Corporation'
        WHEN 1032 THEN 'Big Cheese Studio'
        WHEN 1033 THEN 'Red Dot Games'
        WHEN 1034 THEN 'Red Dot Games'
        WHEN 1035 THEN 'DRAGO entertainment'
        WHEN 1036 THEN 'Cheesecake Dev'
        WHEN 1037 THEN 'Cheesecake Dev'
        WHEN 1038 THEN 'Genesz'
        WHEN 1039 THEN 'Amistech Games'
        WHEN 1040 THEN 'Bugbear Entertainment'
        WHEN 1041 THEN 'Bugbear Entertainment'
        WHEN 1042 THEN 'Bugbear Entertainment'
        WHEN 1043 THEN 'Bugbear Entertainment'
        WHEN 1044 THEN 'Bugbear Entertainment'
        WHEN 1045 THEN 'Criterion Games'
        WHEN 1046 THEN 'Codemasters'
        WHEN 1047 THEN 'Codemasters'
        WHEN 1048 THEN 'Codemasters'
        WHEN 1049 THEN 'Codemasters'
        WHEN 1050 THEN 'Frontier Developments'
        WHEN 1051 THEN 'Frontier Developments'
        WHEN 1052 THEN 'Frontier Developments'
        WHEN 1053 THEN 'Sports Interactive'
        WHEN 1054 THEN 'Sports Interactive'
        WHEN 1055 THEN 'Sports Interactive'
        WHEN 1056 THEN 'Sports Interactive'
        WHEN 1057 THEN 'Paradox Development Studio'
        WHEN 1058 THEN 'Paradox Development Studio'
        WHEN 1059 THEN 'Paradox Development Studio'
        WHEN 1060 THEN 'Paradox Development Studio'
        WHEN 1061 THEN 'Paradox Development Studio'
        WHEN 1062 THEN 'Paradox Development Studio'
        WHEN 1063 THEN 'Paradox Development Studio'
        WHEN 1064 THEN 'Monte Cristo'
        WHEN 1065 THEN 'Paradox Development Studio'
        WHEN 1066 THEN 'Paradox Development Studio'
        WHEN 1067 THEN 'Paradox Development Studio'
        WHEN 1068 THEN 'Relic Entertainment'
        WHEN 1069 THEN 'Relic Entertainment'
        WHEN 1070 THEN 'Relic Entertainment'
        WHEN 1071 THEN 'Westwood Studios'
        WHEN 1072 THEN 'Westwood Studios'
        WHEN 1073 THEN 'Westwood Studios'
        WHEN 1074 THEN 'Westwood Studios'
        WHEN 1075 THEN 'Westwood Studios'
        WHEN 1076 THEN 'Westwood Studios'
        WHEN 1077 THEN 'Blizzard Entertainment'
        WHEN 1078 THEN 'Blizzard Entertainment'
        WHEN 1079 THEN 'Blizzard Entertainment'
        WHEN 1080 THEN 'Blizzard Entertainment'
        WHEN 1081 THEN 'Blizzard Entertainment'
        WHEN 1082 THEN 'Blizzard Entertainment'
        WHEN 1083 THEN 'Worlds Edge'
        WHEN 1084 THEN 'Worlds Edge'
        WHEN 1085 THEN 'Worlds Edge'
        WHEN 1086 THEN 'Worlds Edge'
        WHEN 1087 THEN 'Firefly Studios'
        WHEN 1088 THEN 'Firefly Studios'
        WHEN 1089 THEN 'Firefly Studios'
        WHEN 1090 THEN 'Firefly Studios'
        WHEN 1091 THEN 'Firefly Studios'
        WHEN 1092 THEN 'Firefly Studios'
        WHEN 1093 THEN 'Firefly Studios'
        WHEN 1094 THEN 'Relic Entertainment'
        WHEN 1095 THEN 'Relic Entertainment'
        WHEN 1096 THEN 'Best Way'
        WHEN 1097 THEN 'Best Way'
        WHEN 1098 THEN 'Best Way'
        WHEN 1099 THEN 'Best Way'
        WHEN 1100 THEN 'Blackbird Interactive'
        ELSE developer
    END,
    publisher = CASE id
        WHEN 1 THEN 'Riot Games'
        WHEN 2 THEN 'Valve'
        WHEN 3 THEN 'Xbox Game Studios'
        WHEN 4 THEN 'Rockstar Games'
        WHEN 5 THEN 'Valve'
        WHEN 6 THEN 'Riot Games'
        WHEN 7 THEN 'Activision'
        WHEN 8 THEN 'SCS Software'
        WHEN 9 THEN 'Electronic Arts'
        WHEN 10 THEN 'Epic Games'
        WHEN 11 THEN 'Blizzard Entertainment'
        WHEN 12 THEN 'Epic Games'
        WHEN 13 THEN 'Krafton'
        WHEN 14 THEN 'Ubisoft'
        WHEN 15 THEN 'Embark Studios'
        WHEN 16 THEN 'Bungie'
        WHEN 17 THEN 'Digital Extremes'
        WHEN 18 THEN 'Grinding Gear Games'
        WHEN 19 THEN 'Grinding Gear Games'
        WHEN 20 THEN 'Valve'
        WHEN 21 THEN 'Valve'
        WHEN 22 THEN 'Re-Logic'
        WHEN 23 THEN 'ConcernedApe'
        WHEN 24 THEN 'Facepunch Studios'
        WHEN 25 THEN 'Snail Games'
        WHEN 26 THEN 'Snail Games'
        WHEN 27 THEN 'Bohemia Interactive'
        WHEN 28 THEN 'Smartly Dressed Games'
        WHEN 29 THEN 'The Fun Pimps'
        WHEN 30 THEN 'Klei Entertainment'
        WHEN 31 THEN 'Pocketpair'
        WHEN 32 THEN 'Keen Games'
        WHEN 33 THEN 'Coffee Stain Publishing'
        WHEN 34 THEN 'Axolot Games'
        WHEN 35 THEN 'Unknown Worlds Entertainment'
        WHEN 36 THEN 'Unknown Worlds Entertainment'
        WHEN 37 THEN 'Endnight Games'
        WHEN 38 THEN 'Newnight'
        WHEN 39 THEN 'Xbox Game Studios'
        WHEN 40 THEN 'Devolver Digital'
        WHEN 41 THEN 'Hello Games'
        WHEN 42 THEN 'Xbox Game Studios'
        WHEN 43 THEN 'Coffee Stain Publishing'
        WHEN 44 THEN 'Valve'
        WHEN 45 THEN 'Tripwire Interactive'
        WHEN 46 THEN 'Starbreeze Studios'
        WHEN 47 THEN 'Deep Silver'
        WHEN 48 THEN 'Behaviour Interactive'
        WHEN 49 THEN 'Kinetic Games'
        WHEN 50 THEN 'Zeekerss'
        WHEN 51 THEN 'Landfall Games'
        WHEN 52 THEN 'Innersloth'
        WHEN 53 THEN 'Epic Games'
        WHEN 54 THEN 'Curve Games'
        WHEN 55 THEN 'Electronic Arts'
        WHEN 56 THEN 'Electronic Arts'
        WHEN 57 THEN 'Electronic Arts'
        WHEN 58 THEN 'Electronic Arts'
        WHEN 59 THEN 'Valve'
        WHEN 60 THEN 'Valve'
        WHEN 61 THEN 'Valve'
        WHEN 62 THEN 'Valve'
        WHEN 63 THEN 'Valve'
        WHEN 64 THEN 'Valve'
        WHEN 65 THEN 'Valve'
        WHEN 66 THEN 'Crowbar Collective'
        WHEN 67 THEN 'Valve'
        WHEN 68 THEN 'Valve'
        WHEN 69 THEN 'Valve'
        WHEN 70 THEN 'Valve'
        WHEN 71 THEN 'Valve'
        WHEN 72 THEN 'Valve'
        WHEN 73 THEN 'Valve'
        WHEN 74 THEN 'Valve'
        WHEN 75 THEN 'Xbox Game Studios'
        WHEN 76 THEN 'Xbox Game Studios'
        WHEN 77 THEN 'Xbox Game Studios'
        WHEN 78 THEN 'Xbox Game Studios'
        WHEN 79 THEN 'Blizzard Entertainment'
        WHEN 80 THEN 'Blizzard Entertainment'
        WHEN 81 THEN 'Blizzard Entertainment'
        WHEN 82 THEN 'Blizzard Entertainment'
        WHEN 83 THEN 'Blizzard Entertainment'
        WHEN 84 THEN 'Blizzard Entertainment'
        WHEN 85 THEN 'Blizzard Entertainment'
        WHEN 86 THEN 'Blizzard Entertainment'
        WHEN 87 THEN 'Blizzard Entertainment'
        WHEN 88 THEN 'Blizzard Entertainment'
        WHEN 89 THEN 'Blizzard Entertainment'
        WHEN 90 THEN 'Blizzard Entertainment'
        WHEN 91 THEN 'Blizzard Entertainment'
        WHEN 92 THEN 'NCsoft'
        WHEN 93 THEN 'NCsoft'
        WHEN 94 THEN 'Bethesda Softworks'
        WHEN 95 THEN 'Bethesda Softworks'
        WHEN 96 THEN 'Bethesda Softworks'
        WHEN 97 THEN 'Bethesda Softworks'
        WHEN 98 THEN 'Bethesda Softworks'
        WHEN 99 THEN 'Bethesda Softworks'
        WHEN 100 THEN 'Bethesda Softworks'
        WHEN 101 THEN 'Bethesda Softworks'
        WHEN 102 THEN 'Bethesda Softworks'
        WHEN 103 THEN 'Bethesda Softworks'
        WHEN 104 THEN 'Bethesda Softworks'
        WHEN 105 THEN 'Private Division'
        WHEN 106 THEN 'Private Division'
        WHEN 107 THEN 'Interplay Entertainment'
        WHEN 108 THEN 'Interplay Entertainment'
        WHEN 109 THEN 'Interplay Entertainment'
        WHEN 110 THEN 'Larian Studios'
        WHEN 111 THEN 'Larian Studios'
        WHEN 112 THEN 'Electronic Arts'
        WHEN 113 THEN 'Electronic Arts'
        WHEN 114 THEN 'Electronic Arts'
        WHEN 115 THEN 'Electronic Arts'
        WHEN 116 THEN 'Electronic Arts'
        WHEN 117 THEN 'Electronic Arts'
        WHEN 118 THEN 'Electronic Arts'
        WHEN 119 THEN 'Electronic Arts'
        WHEN 120 THEN 'LucasArts'
        WHEN 121 THEN 'LucasArts'
        WHEN 122 THEN 'Electronic Arts'
        WHEN 123 THEN 'Electronic Arts'
        WHEN 124 THEN 'Ubisoft'
        WHEN 125 THEN 'Electronic Arts'
        WHEN 126 THEN 'Electronic Arts'
        WHEN 127 THEN 'Electronic Arts'
        WHEN 128 THEN 'Electronic Arts'
        WHEN 129 THEN 'Rockstar Games'
        WHEN 130 THEN 'Rockstar Games'
        WHEN 131 THEN 'Rockstar Games'
        WHEN 132 THEN 'Rockstar Games'
        WHEN 133 THEN 'Rockstar Games'
        WHEN 134 THEN 'Rockstar Games'
        WHEN 135 THEN 'Rockstar Games'
        WHEN 136 THEN 'Rockstar Games'
        WHEN 137 THEN 'Rockstar Games'
        WHEN 138 THEN 'Rockstar Games'
        WHEN 139 THEN 'Rockstar Games'
        WHEN 140 THEN 'Rockstar Games'
        WHEN 141 THEN 'Rockstar Games'
        WHEN 142 THEN 'Rockstar Games'
        WHEN 143 THEN 'Rockstar Games'
        WHEN 144 THEN 'Rockstar Games'
        WHEN 145 THEN 'Rockstar Games'
        WHEN 146 THEN 'Remedy Entertainment'
        WHEN 147 THEN 'Remedy Entertainment'
        WHEN 148 THEN 'Remedy Entertainment'
        WHEN 149 THEN 'Remedy Entertainment'
        WHEN 150 THEN 'Electronic Arts'
        WHEN 151 THEN 'Electronic Arts'
        WHEN 152 THEN 'Electronic Arts'
        WHEN 153 THEN 'Electronic Arts'
        WHEN 154 THEN 'Capcom'
        WHEN 155 THEN 'Capcom'
        WHEN 156 THEN 'Capcom'
        WHEN 157 THEN 'Capcom'
        WHEN 158 THEN 'Capcom'
        WHEN 159 THEN 'Capcom'
        WHEN 160 THEN 'Capcom'
        WHEN 161 THEN 'Capcom'
        WHEN 162 THEN 'Capcom'
        WHEN 163 THEN 'Capcom'
        WHEN 164 THEN 'Capcom'
        WHEN 165 THEN 'Capcom'
        WHEN 166 THEN 'Capcom'
        WHEN 167 THEN 'Capcom'
        WHEN 168 THEN 'Capcom'
        WHEN 169 THEN 'Capcom'
        WHEN 170 THEN 'Capcom'
        WHEN 171 THEN 'Capcom'
        WHEN 172 THEN 'Capcom'
        WHEN 173 THEN 'Capcom'
        WHEN 174 THEN 'Capcom'
        WHEN 175 THEN 'Capcom'
        WHEN 176 THEN 'Capcom'
        WHEN 177 THEN 'Capcom'
        WHEN 178 THEN 'Capcom'
        WHEN 179 THEN 'Capcom'
        WHEN 180 THEN 'Capcom'
        WHEN 181 THEN 'Capcom'
        WHEN 182 THEN 'Capcom'
        WHEN 183 THEN 'Capcom'
        WHEN 184 THEN 'Capcom'
        WHEN 185 THEN 'Capcom'
        WHEN 186 THEN 'Capcom'
        WHEN 187 THEN 'Capcom'
        WHEN 188 THEN 'Capcom'
        WHEN 189 THEN 'Capcom'
        WHEN 190 THEN 'Bandai Namco Entertainment'
        WHEN 191 THEN 'Bandai Namco Entertainment'
        WHEN 192 THEN 'Bandai Namco Entertainment'
        WHEN 193 THEN 'Bandai Namco Entertainment'
        WHEN 194 THEN 'Bandai Namco Entertainment'
        WHEN 195 THEN 'Bandai Namco Entertainment'
        WHEN 196 THEN 'Bandai Namco Entertainment'
        WHEN 197 THEN 'Bandai Namco Entertainment'
        WHEN 198 THEN 'Warner Bros. Games'
        WHEN 199 THEN 'Warner Bros. Games'
        WHEN 200 THEN 'Warner Bros. Games'
        WHEN 201 THEN 'Warner Bros. Games'
        WHEN 202 THEN 'Warner Bros. Games'
        WHEN 203 THEN 'Warner Bros. Games'
        WHEN 204 THEN 'Warner Bros. Games'
        WHEN 205 THEN 'Warner Bros. Games'
        WHEN 206 THEN 'Warner Bros. Games'
        WHEN 207 THEN 'Warner Bros. Games'
        WHEN 208 THEN 'Warner Bros. Games'
        WHEN 209 THEN 'Arc System Works'
        WHEN 210 THEN 'Arc System Works'
        WHEN 211 THEN 'Arc System Works'
        WHEN 212 THEN 'Arc System Works'
        WHEN 213 THEN 'Arc System Works'
        WHEN 214 THEN 'Arc System Works'
        WHEN 215 THEN 'Arc System Works'
        WHEN 216 THEN 'Arc System Works'
        WHEN 217 THEN 'SNK'
        WHEN 218 THEN 'SNK'
        WHEN 219 THEN 'SNK'
        WHEN 220 THEN 'Bandai Namco Entertainment'
        WHEN 221 THEN 'Bandai Namco Entertainment'
        WHEN 222 THEN 'Bandai Namco Entertainment'
        WHEN 223 THEN 'Bandai Namco Entertainment'
        WHEN 224 THEN 'Bandai Namco Entertainment'
        WHEN 225 THEN 'Bandai Namco Entertainment'
        WHEN 226 THEN 'Koei Tecmo'
        WHEN 227 THEN 'Koei Tecmo'
        WHEN 228 THEN 'Sega'
        WHEN 229 THEN 'Xbox Game Studios'
        WHEN 230 THEN 'SNK'
        WHEN 231 THEN 'Cygames'
        WHEN 232 THEN 'Cygames'
        WHEN 233 THEN 'Capcom'
        WHEN 234 THEN 'Capcom'
        WHEN 235 THEN 'Capcom'
        WHEN 236 THEN 'Capcom'
        WHEN 237 THEN 'Bandai Namco Entertainment'
        WHEN 238 THEN 'Bandai Namco Entertainment'
        WHEN 239 THEN 'Bandai Namco Entertainment'
        WHEN 240 THEN 'Bandai Namco Entertainment'
        WHEN 241 THEN 'Bandai Namco Entertainment'
        WHEN 242 THEN 'Bandai Namco Entertainment'
        WHEN 243 THEN 'Bandai Namco Entertainment'
        WHEN 244 THEN 'Bandai Namco Entertainment'
        WHEN 245 THEN 'Bandai Namco Entertainment'
        WHEN 246 THEN 'Bandai Namco Entertainment'
        WHEN 247 THEN 'Bandai Namco Entertainment'
        WHEN 248 THEN 'Bandai Namco Entertainment'
        WHEN 249 THEN 'Bandai Namco Entertainment'
        WHEN 250 THEN 'Sega'
        WHEN 251 THEN 'Sega'
        WHEN 252 THEN 'Sega'
        WHEN 253 THEN 'Sega'
        WHEN 254 THEN 'Sega'
        WHEN 255 THEN 'Sega'
        WHEN 256 THEN 'Sega'
        WHEN 257 THEN 'Sega'
        WHEN 258 THEN 'Sega'
        WHEN 259 THEN 'Square Enix'
        WHEN 260 THEN 'Square Enix'
        WHEN 261 THEN 'Square Enix'
        WHEN 262 THEN 'Square Enix'
        WHEN 263 THEN 'Square Enix'
        WHEN 264 THEN 'Square Enix'
        WHEN 265 THEN 'Square Enix'
        WHEN 266 THEN 'Square Enix'
        WHEN 267 THEN 'Square Enix'
        WHEN 268 THEN 'Square Enix'
        WHEN 269 THEN 'Square Enix'
        WHEN 270 THEN 'Square Enix'
        WHEN 271 THEN 'Square Enix'
        WHEN 272 THEN 'Square Enix'
        WHEN 273 THEN 'Square Enix'
        WHEN 274 THEN 'Square Enix'
        WHEN 275 THEN 'Square Enix'
        WHEN 276 THEN 'Square Enix'
        WHEN 277 THEN 'Square Enix'
        WHEN 278 THEN 'Square Enix'
        WHEN 279 THEN 'Square Enix'
        WHEN 280 THEN 'Square Enix'
        WHEN 281 THEN 'Square Enix'
        WHEN 282 THEN 'Square Enix'
        WHEN 283 THEN 'Square Enix'
        WHEN 284 THEN 'Square Enix'
        WHEN 285 THEN 'Square Enix'
        WHEN 286 THEN 'Square Enix'
        WHEN 287 THEN 'Square Enix'
        WHEN 288 THEN 'Square Enix'
        WHEN 289 THEN 'Square Enix'
        WHEN 290 THEN 'Square Enix'
        WHEN 291 THEN 'Square Enix'
        WHEN 292 THEN 'Square Enix'
        WHEN 293 THEN 'Square Enix'
        WHEN 294 THEN 'Square Enix'
        WHEN 295 THEN 'Square Enix'
        WHEN 296 THEN 'Square Enix'
        WHEN 297 THEN 'Square Enix'
        WHEN 298 THEN 'Square Enix'
        WHEN 299 THEN 'Square Enix'
        WHEN 300 THEN 'Square Enix'
        WHEN 301 THEN 'Sega'
        WHEN 302 THEN 'Sega'
        WHEN 303 THEN 'Sega'
        WHEN 304 THEN 'Sega'
        WHEN 305 THEN 'Sega'
        WHEN 306 THEN 'Sega'
        WHEN 307 THEN 'Sega'
        WHEN 308 THEN 'Sega'
        WHEN 309 THEN 'Sega'
        WHEN 310 THEN 'Sega'
        WHEN 311 THEN 'Sega'
        WHEN 312 THEN 'Sega'
        WHEN 313 THEN 'Sega'
        WHEN 314 THEN 'Bandai Namco Entertainment'
        WHEN 315 THEN 'Bandai Namco Entertainment'
        WHEN 316 THEN 'Bandai Namco Entertainment'
        WHEN 317 THEN 'Bandai Namco Entertainment'
        WHEN 318 THEN 'Bandai Namco Entertainment'
        WHEN 319 THEN 'NIS America'
        WHEN 320 THEN 'NIS America'
        WHEN 321 THEN 'NIS America'
        WHEN 322 THEN 'NIS America'
        WHEN 323 THEN 'NIS America'
        WHEN 324 THEN 'NIS America'
        WHEN 325 THEN 'NIS America'
        WHEN 326 THEN 'NIS America'
        WHEN 327 THEN 'NIS America'
        WHEN 328 THEN 'Sega'
        WHEN 329 THEN 'Bandai Namco Entertainment'
        WHEN 330 THEN 'Bandai Namco Entertainment'
        WHEN 331 THEN 'Bandai Namco Entertainment'
        WHEN 332 THEN 'Bandai Namco Entertainment'
        WHEN 333 THEN 'Bandai Namco Entertainment'
        WHEN 334 THEN 'Bandai Namco Entertainment'
        WHEN 335 THEN 'Bandai Namco Entertainment'
        WHEN 336 THEN 'Bandai Namco Entertainment'
        WHEN 337 THEN 'Bandai Namco Entertainment'
        WHEN 338 THEN 'Neowiz'
        WHEN 339 THEN 'CI Games'
        WHEN 340 THEN 'CI Games'
        WHEN 341 THEN 'Koei Tecmo'
        WHEN 342 THEN 'Koei Tecmo'
        WHEN 343 THEN 'Koei Tecmo'
        WHEN 344 THEN 'Koei Tecmo'
        WHEN 345 THEN 'Bandai Namco Entertainment'
        WHEN 346 THEN 'Gearbox Publishing'
        WHEN 347 THEN 'Gearbox Publishing'
        WHEN 348 THEN 'Playstack'
        WHEN 349 THEN 'Focus Entertainment'
        WHEN 350 THEN 'Focus Entertainment'
        WHEN 351 THEN 'Annapurna Interactive'
        WHEN 352 THEN 'Ska Studios'
        WHEN 353 THEN 'Ska Studios'
        WHEN 354 THEN 'Supergiant Games'
        WHEN 355 THEN 'Supergiant Games'
        WHEN 356 THEN 'Motion Twin'
        WHEN 357 THEN 'Team Cherry'
        WHEN 358 THEN 'Team Cherry'
        WHEN 359 THEN 'Studio MDHR'
        WHEN 360 THEN 'Xbox Game Studios'
        WHEN 361 THEN 'Xbox Game Studios'
        WHEN 362 THEN 'Maddy Makes Games'
        WHEN 363 THEN 'Yacht Club Games'
        WHEN 364 THEN 'Devolver Digital'
        WHEN 365 THEN 'Team17'
        WHEN 366 THEN 'Team17'
        WHEN 367 THEN 'Thomas Happ Games'
        WHEN 368 THEN 'Thomas Happ Games'
        WHEN 369 THEN 'Heart Machine'
        WHEN 370 THEN 'Heart Machine'
        WHEN 371 THEN 'Devolver Digital'
        WHEN 372 THEN 'Devolver Digital'
        WHEN 373 THEN 'Devolver Digital'
        WHEN 374 THEN 'Devolver Digital'
        WHEN 375 THEN 'Devolver Digital'
        WHEN 376 THEN 'Nicalis'
        WHEN 377 THEN 'Nicalis'
        WHEN 378 THEN 'Gearbox Publishing'
        WHEN 379 THEN 'Gearbox Publishing'
        WHEN 380 THEN 'Gearbox Publishing'
        WHEN 381 THEN 'Cellar Door Games'
        WHEN 382 THEN 'Cellar Door Games'
        WHEN 383 THEN 'poncle'
        WHEN 384 THEN 'Blobfish'
        WHEN 385 THEN 'Chasing Carrots'
        WHEN 386 THEN 'Playstack'
        WHEN 387 THEN 'Mega Crit'
        WHEN 388 THEN 'Devolver Digital'
        WHEN 389 THEN 'Devolver Digital'
        WHEN 390 THEN 'Red Hook Studios'
        WHEN 391 THEN 'Red Hook Studios'
        WHEN 392 THEN 'Subset Games'
        WHEN 393 THEN 'Subset Games'
        WHEN 394 THEN '3909 LLC'
        WHEN 395 THEN '3909 LLC'
        WHEN 396 THEN 'Toby Fox'
        WHEN 397 THEN 'Toby Fox'
        WHEN 398 THEN 'ZA/UM'
        WHEN 399 THEN 'Annapurna Interactive'
        WHEN 400 THEN 'Annapurna Interactive'
        WHEN 401 THEN 'Crows Crows Crows'
        WHEN 402 THEN 'Crows Crows Crows'
        WHEN 403 THEN 'Annapurna Interactive'
        WHEN 404 THEN 'Campo Santo'
        WHEN 405 THEN 'Fullbright'
        WHEN 406 THEN 'Netflix Games'
        WHEN 407 THEN 'Netflix Games'
        WHEN 408 THEN 'Finji'
        WHEN 409 THEN 'Square Enix'
        WHEN 410 THEN 'Square Enix'
        WHEN 411 THEN 'Square Enix'
        WHEN 412 THEN 'Square Enix'
        WHEN 413 THEN 'Xbox Game Studios'
        WHEN 414 THEN 'Bandai Namco Entertainment'
        WHEN 415 THEN 'Ravenscourt'
        WHEN 416 THEN 'Quantic Dream'
        WHEN 417 THEN 'Quantic Dream'
        WHEN 418 THEN 'Quantic Dream'
        WHEN 419 THEN 'Sony Interactive Entertainment'
        WHEN 420 THEN 'Sony Interactive Entertainment'
        WHEN 421 THEN 'Telltale Games'
        WHEN 422 THEN 'Telltale Games'
        WHEN 423 THEN 'Telltale Games'
        WHEN 424 THEN 'Telltale Games'
        WHEN 425 THEN 'Telltale Games'
        WHEN 426 THEN 'Telltale Games'
        WHEN 427 THEN 'Telltale Games'
        WHEN 428 THEN 'Xbox Game Studios'
        WHEN 429 THEN 'Xbox Game Studios'
        WHEN 430 THEN '2K'
        WHEN 431 THEN '2K'
        WHEN 432 THEN '2K'
        WHEN 433 THEN '2K'
        WHEN 434 THEN '2K'
        WHEN 435 THEN '2K'
        WHEN 436 THEN '2K'
        WHEN 437 THEN '2K'
        WHEN 438 THEN '2K'
        WHEN 439 THEN 'Nightdive Studios'
        WHEN 440 THEN 'Nightdive Studios'
        WHEN 441 THEN 'Bethesda Softworks'
        WHEN 442 THEN 'Bethesda Softworks'
        WHEN 443 THEN 'Bethesda Softworks'
        WHEN 444 THEN 'Bethesda Softworks'
        WHEN 445 THEN 'Bethesda Softworks'
        WHEN 446 THEN 'Bethesda Softworks'
        WHEN 447 THEN 'Bethesda Softworks'
        WHEN 448 THEN 'Bethesda Softworks'
        WHEN 449 THEN 'Bethesda Softworks'
        WHEN 450 THEN 'Bethesda Softworks'
        WHEN 451 THEN 'Bethesda Softworks'
        WHEN 452 THEN 'Bethesda Softworks'
        WHEN 453 THEN 'Bethesda Softworks'
        WHEN 454 THEN 'Bethesda Softworks'
        WHEN 455 THEN 'Bethesda Softworks'
        WHEN 456 THEN 'Bethesda Softworks'
        WHEN 457 THEN 'Bethesda Softworks'
        WHEN 458 THEN 'Bethesda Softworks'
        WHEN 459 THEN 'Bethesda Softworks'
        WHEN 460 THEN 'Bethesda Softworks'
        WHEN 461 THEN 'Electronic Arts'
        WHEN 462 THEN 'Electronic Arts'
        WHEN 463 THEN 'Electronic Arts'
        WHEN 464 THEN 'Electronic Arts'
        WHEN 465 THEN 'Electronic Arts'
        WHEN 466 THEN 'Electronic Arts'
        WHEN 467 THEN 'Ubisoft'
        WHEN 468 THEN 'Ubisoft'
        WHEN 469 THEN 'Ubisoft'
        WHEN 470 THEN 'Ubisoft'
        WHEN 471 THEN 'Ubisoft'
        WHEN 472 THEN 'Ubisoft'
        WHEN 473 THEN 'Ubisoft'
        WHEN 474 THEN 'Ubisoft'
        WHEN 475 THEN 'Ubisoft'
        WHEN 476 THEN 'Ubisoft'
        WHEN 477 THEN 'Ubisoft'
        WHEN 478 THEN 'Ubisoft'
        WHEN 479 THEN 'Ubisoft'
        WHEN 480 THEN 'Ubisoft'
        WHEN 481 THEN 'Ubisoft'
        WHEN 482 THEN 'Ubisoft'
        WHEN 483 THEN 'Ubisoft'
        WHEN 484 THEN 'Ubisoft'
        WHEN 485 THEN 'Ubisoft'
        WHEN 486 THEN 'Ubisoft'
        WHEN 487 THEN 'Ubisoft'
        WHEN 488 THEN 'Ubisoft'
        WHEN 489 THEN 'Ubisoft'
        WHEN 490 THEN 'Ubisoft'
        WHEN 491 THEN 'Ubisoft'
        WHEN 492 THEN 'Ubisoft'
        WHEN 493 THEN 'Ubisoft'
        WHEN 494 THEN 'Ubisoft'
        WHEN 495 THEN 'Ubisoft'
        WHEN 496 THEN 'Ubisoft'
        WHEN 497 THEN 'Ubisoft'
        WHEN 498 THEN 'Ubisoft'
        WHEN 499 THEN 'Ubisoft'
        WHEN 500 THEN 'Ubisoft'
        WHEN 501 THEN 'Ubisoft'
        WHEN 502 THEN 'Ubisoft'
        WHEN 503 THEN 'Ubisoft'
        WHEN 504 THEN 'Ubisoft'
        WHEN 505 THEN 'Ubisoft'
        WHEN 506 THEN 'Ubisoft'
        WHEN 507 THEN 'Ubisoft'
        WHEN 508 THEN 'Ubisoft'
        WHEN 509 THEN 'Ubisoft'
        WHEN 510 THEN 'Ubisoft'
        WHEN 511 THEN 'Ubisoft'
        WHEN 512 THEN 'Ubisoft'
        WHEN 513 THEN 'Ubisoft'
        WHEN 514 THEN 'Ubisoft'
        WHEN 515 THEN 'Ubisoft'
        WHEN 516 THEN 'Ubisoft'
        WHEN 517 THEN 'Ubisoft'
        WHEN 518 THEN 'Ubisoft'
        WHEN 519 THEN 'Ubisoft'
        WHEN 520 THEN 'Ubisoft'
        WHEN 521 THEN 'Ubisoft'
        WHEN 522 THEN 'Ubisoft'
        WHEN 523 THEN 'Ubisoft'
        WHEN 524 THEN 'Ubisoft'
        WHEN 525 THEN 'Ubisoft'
        WHEN 526 THEN 'Ubisoft'
        WHEN 527 THEN 'Xbox Game Studios'
        WHEN 528 THEN 'Xbox Game Studios'
        WHEN 529 THEN 'Xbox Game Studios'
        WHEN 530 THEN 'Xbox Game Studios'
        WHEN 531 THEN 'Xbox Game Studios'
        WHEN 532 THEN 'Xbox Game Studios'
        WHEN 533 THEN 'Xbox Game Studios'
        WHEN 534 THEN 'Xbox Game Studios'
        WHEN 535 THEN 'Xbox Game Studios'
        WHEN 536 THEN 'Xbox Game Studios'
        WHEN 537 THEN 'Xbox Game Studios'
        WHEN 538 THEN 'Xbox Game Studios'
        WHEN 539 THEN 'Xbox Game Studios'
        WHEN 540 THEN 'Xbox Game Studios'
        WHEN 541 THEN 'Xbox Game Studios'
        WHEN 542 THEN 'Xbox Game Studios'
        WHEN 543 THEN 'Xbox Game Studios'
        WHEN 544 THEN 'Xbox Game Studios'
        WHEN 545 THEN 'Xbox Game Studios'
        WHEN 546 THEN 'Xbox Game Studios'
        WHEN 547 THEN 'Xbox Game Studios'
        WHEN 548 THEN 'Xbox Game Studios'
        WHEN 549 THEN 'Xbox Game Studios'
        WHEN 550 THEN 'Xbox Game Studios'
        WHEN 551 THEN 'Xbox Game Studios'
        WHEN 552 THEN 'Xbox Game Studios'
        WHEN 553 THEN 'Xbox Game Studios'
        WHEN 554 THEN 'Xbox Game Studios'
        WHEN 555 THEN 'Xbox Game Studios'
        WHEN 556 THEN 'Bethesda Softworks'
        WHEN 557 THEN 'Xbox Game Studios'
        WHEN 558 THEN 'Xbox Game Studios'
        WHEN 559 THEN 'Xbox Game Studios'
        WHEN 560 THEN 'Microsoft Studios'
        WHEN 561 THEN 'Microsoft Studios'
        WHEN 562 THEN 'Xbox Game Studios'
        WHEN 563 THEN 'Xbox Game Studios'
        WHEN 564 THEN 'Xbox Game Studios'
        WHEN 565 THEN 'Xbox Game Studios'
        WHEN 566 THEN 'Xbox Game Studios'
        WHEN 567 THEN 'Xbox Game Studios'
        WHEN 568 THEN 'Xbox Game Studios'
        WHEN 569 THEN 'Xbox Game Studios'
        WHEN 570 THEN 'Paradox Interactive'
        WHEN 571 THEN 'Paradox Interactive'
        WHEN 572 THEN 'Paradox Interactive'
        WHEN 573 THEN 'META Publishing'
        WHEN 574 THEN 'META Publishing'
        WHEN 575 THEN 'Nacon'
        WHEN 576 THEN 'Sega'
        WHEN 577 THEN 'Sega'
        WHEN 578 THEN 'Fatshark'
        WHEN 579 THEN 'Fatshark'
        WHEN 580 THEN 'Fatshark'
        WHEN 581 THEN 'Sega'
        WHEN 582 THEN 'Sega'
        WHEN 583 THEN 'Sega'
        WHEN 584 THEN 'Sega'
        WHEN 585 THEN 'Sega'
        WHEN 586 THEN 'Sega'
        WHEN 587 THEN 'Sega'
        WHEN 588 THEN 'Sega'
        WHEN 589 THEN 'Sega'
        WHEN 590 THEN 'Sega'
        WHEN 591 THEN 'Sega'
        WHEN 592 THEN '2K Games'
        WHEN 593 THEN '2K Games'
        WHEN 594 THEN '2K Games'
        WHEN 595 THEN '2K Games'
        WHEN 596 THEN '2K Games'
        WHEN 597 THEN '2K Games'
        WHEN 598 THEN '2K'
        WHEN 599 THEN '2K Games'
        WHEN 600 THEN '2K Games'
        WHEN 601 THEN '2K Games'
        WHEN 602 THEN '2K Games'
        WHEN 603 THEN 'Electronic Arts'
        WHEN 604 THEN 'Electronic Arts'
        WHEN 605 THEN 'Electronic Arts'
        WHEN 606 THEN 'Electronic Arts'
        WHEN 607 THEN 'Electronic Arts'
        WHEN 608 THEN 'Electronic Arts'
        WHEN 609 THEN 'Electronic Arts'
        WHEN 610 THEN 'Electronic Arts'
        WHEN 611 THEN 'Paradox Interactive'
        WHEN 612 THEN 'Paradox Interactive'
        WHEN 613 THEN 'Focus Home Interactive'
        WHEN 614 THEN 'Frontier Developments'
        WHEN 615 THEN 'Frontier Developments'
        WHEN 616 THEN 'Frontier Developments'
        WHEN 617 THEN 'Frontier Developments'
        WHEN 618 THEN 'Frontier Developments'
        WHEN 619 THEN 'Microsoft Studios'
        WHEN 620 THEN 'Atari'
        WHEN 621 THEN 'Atari'
        WHEN 622 THEN 'Frontier Developments'
        WHEN 623 THEN 'Sega'
        WHEN 624 THEN 'Sega'
        WHEN 625 THEN 'GIANTS Software'
        WHEN 626 THEN 'GIANTS Software'
        WHEN 627 THEN 'GIANTS Software'
        WHEN 628 THEN 'GIANTS Software'
        WHEN 629 THEN 'SCS Software'
        WHEN 630 THEN 'Dovetail Games'
        WHEN 631 THEN 'Dovetail Games'
        WHEN 632 THEN 'Dovetail Games'
        WHEN 633 THEN 'Dovetail Games'
        WHEN 634 THEN 'Dovetail Games'
        WHEN 635 THEN 'Dovetail Games'
        WHEN 636 THEN 'Altfuture'
        WHEN 637 THEN 'Focus Entertainment'
        WHEN 638 THEN 'Focus Entertainment'
        WHEN 639 THEN 'Focus Entertainment'
        WHEN 640 THEN 'BeamNG'
        WHEN 641 THEN 'CarX Technologies'
        WHEN 642 THEN '505 Games'
        WHEN 643 THEN '505 Games'
        WHEN 644 THEN 'Reiza Studios'
        WHEN 645 THEN 'Reiza Studios'
        WHEN 646 THEN 'iRacing.com Motorsport Simulations'
        WHEN 647 THEN 'Studio 397'
        WHEN 648 THEN 'Studio 397'
        WHEN 649 THEN 'Bandai Namco Entertainment'
        WHEN 650 THEN 'Bandai Namco Entertainment'
        WHEN 651 THEN 'Bandai Namco Entertainment'
        WHEN 652 THEN 'Codemasters'
        WHEN 653 THEN 'Codemasters'
        WHEN 654 THEN 'Codemasters'
        WHEN 655 THEN 'Codemasters'
        WHEN 656 THEN 'Codemasters'
        WHEN 657 THEN 'Nacon'
        WHEN 658 THEN 'Nacon'
        WHEN 659 THEN 'Nacon'
        WHEN 660 THEN 'Nacon'
        WHEN 661 THEN 'Electronic Arts'
        WHEN 662 THEN 'Electronic Arts'
        WHEN 663 THEN 'Electronic Arts'
        WHEN 664 THEN 'Electronic Arts'
        WHEN 665 THEN 'Electronic Arts'
        WHEN 666 THEN 'Electronic Arts'
        WHEN 667 THEN 'Electronic Arts'
        WHEN 668 THEN 'Electronic Arts'
        WHEN 669 THEN 'Electronic Arts'
        WHEN 670 THEN 'Milestone'
        WHEN 671 THEN 'Milestone'
        WHEN 672 THEN 'Milestone'
        WHEN 673 THEN 'Milestone'
        WHEN 674 THEN 'Milestone'
        WHEN 675 THEN 'Milestone'
        WHEN 676 THEN '2K'
        WHEN 677 THEN '2K'
        WHEN 678 THEN '2K'
        WHEN 679 THEN '2K'
        WHEN 680 THEN '2K'
        WHEN 681 THEN '2K'
        WHEN 682 THEN '2K'
        WHEN 683 THEN '2K'
        WHEN 684 THEN '2K'
        WHEN 685 THEN '2K'
        WHEN 686 THEN '2K'
        WHEN 687 THEN '2K'
        WHEN 688 THEN '2K'
        WHEN 689 THEN 'Electronic Arts'
        WHEN 690 THEN 'Electronic Arts'
        WHEN 691 THEN 'Electronic Arts'
        WHEN 692 THEN 'Electronic Arts'
        WHEN 693 THEN 'Electronic Arts'
        WHEN 694 THEN 'Electronic Arts'
        WHEN 695 THEN 'Electronic Arts'
        WHEN 696 THEN 'Electronic Arts'
        WHEN 697 THEN 'Konami'
        WHEN 698 THEN 'Konami'
        WHEN 699 THEN 'Konami'
        WHEN 700 THEN 'Electronic Arts'
        WHEN 701 THEN 'Electronic Arts'
        WHEN 702 THEN 'Electronic Arts'
        WHEN 703 THEN 'Electronic Arts'
        WHEN 704 THEN 'Electronic Arts'
        WHEN 705 THEN 'Electronic Arts'
        WHEN 706 THEN 'Electronic Arts'
        WHEN 707 THEN 'Electronic Arts'
        WHEN 708 THEN 'Sony Interactive Entertainment'
        WHEN 709 THEN 'Sony Interactive Entertainment'
        WHEN 710 THEN 'Sony Interactive Entertainment'
        WHEN 711 THEN 'Sony Interactive Entertainment'
        WHEN 712 THEN 'Activision'
        WHEN 713 THEN 'Activision'
        WHEN 714 THEN 'Electronic Arts'
        WHEN 715 THEN 'Electronic Arts'
        WHEN 716 THEN 'Electronic Arts'
        WHEN 717 THEN 'Electronic Arts'
        WHEN 718 THEN 'Electronic Arts'
        WHEN 719 THEN 'Electronic Arts'
        WHEN 720 THEN 'Electronic Arts'
        WHEN 721 THEN 'Electronic Arts'
        WHEN 722 THEN 'Electronic Arts'
        WHEN 723 THEN 'Electronic Arts'
        WHEN 724 THEN 'Electronic Arts'
        WHEN 725 THEN 'Electronic Arts'
        WHEN 726 THEN 'Electronic Arts'
        WHEN 727 THEN 'Electronic Arts'
        WHEN 728 THEN 'Electronic Arts'
        WHEN 729 THEN 'Electronic Arts'
        WHEN 730 THEN 'Electronic Arts'
        WHEN 731 THEN 'Electronic Arts'
        WHEN 732 THEN 'Electronic Arts'
        WHEN 733 THEN 'Rockstar Games'
        WHEN 734 THEN 'Rockstar Games'
        WHEN 735 THEN 'Rockstar Games'
        WHEN 736 THEN 'Rockstar Games'
        WHEN 737 THEN 'Sony Interactive Entertainment'
        WHEN 738 THEN 'Sony Interactive Entertainment'
        WHEN 739 THEN 'Sony Interactive Entertainment'
        WHEN 740 THEN 'Sony Interactive Entertainment'
        WHEN 741 THEN 'Sony Interactive Entertainment'
        WHEN 742 THEN 'Square Enix'
        WHEN 743 THEN 'Square Enix'
        WHEN 744 THEN 'Activision'
        WHEN 745 THEN 'Activision'
        WHEN 746 THEN 'Activision'
        WHEN 747 THEN 'Activision'
        WHEN 748 THEN 'Activision'
        WHEN 749 THEN 'Activision'
        WHEN 750 THEN '2K'
        WHEN 751 THEN 'Warner Bros. Games'
        WHEN 752 THEN 'Warner Bros. Games'
        WHEN 753 THEN 'Warner Bros. Games'
        WHEN 754 THEN 'Warner Bros. Games'
        WHEN 755 THEN 'Warner Bros. Games'
        WHEN 756 THEN 'Warner Bros. Games'
        WHEN 757 THEN 'Warner Bros. Games'
        WHEN 758 THEN 'Warner Bros. Interactive Entertainment'
        WHEN 759 THEN 'Atari'
        WHEN 760 THEN 'Warner Bros. Games'
        WHEN 761 THEN 'Warner Bros. Games'
        WHEN 762 THEN 'Warner Bros. Games'
        WHEN 763 THEN 'Warner Bros. Games'
        WHEN 764 THEN 'Warner Bros. Games'
        WHEN 765 THEN 'Warner Bros. Games'
        WHEN 766 THEN 'Warner Bros. Games'
        WHEN 767 THEN 'Warner Bros. Games'
        WHEN 768 THEN 'Warner Bros. Games'
        WHEN 769 THEN 'Warner Bros. Games'
        WHEN 770 THEN 'Warner Bros. Games'
        WHEN 771 THEN 'Warner Bros. Games'
        WHEN 772 THEN 'Warner Bros. Games'
        WHEN 773 THEN 'Warner Bros. Games'
        WHEN 774 THEN 'Warner Bros. Games'
        WHEN 775 THEN 'Warner Bros. Games'
        WHEN 776 THEN 'Warner Bros. Games'
        WHEN 777 THEN 'Warner Bros. Games'
        WHEN 778 THEN 'Warner Bros. Games'
        WHEN 779 THEN 'Warner Bros. Games'
        WHEN 780 THEN 'Square Enix'
        WHEN 781 THEN 'Square Enix'
        WHEN 782 THEN 'Square Enix'
        WHEN 783 THEN 'Square Enix'
        WHEN 784 THEN 'Square Enix'
        WHEN 785 THEN 'Square Enix'
        WHEN 786 THEN 'Square Enix'
        WHEN 787 THEN 'Sony Interactive Entertainment'
        WHEN 788 THEN 'Sony Interactive Entertainment'
        WHEN 789 THEN 'Sony Interactive Entertainment'
        WHEN 790 THEN 'Sony Interactive Entertainment'
        WHEN 791 THEN 'Sony Interactive Entertainment'
        WHEN 792 THEN 'Sony Interactive Entertainment'
        WHEN 793 THEN 'Sony Interactive Entertainment'
        WHEN 794 THEN 'Sony Interactive Entertainment'
        WHEN 795 THEN 'Sony Interactive Entertainment'
        WHEN 796 THEN 'Sony Interactive Entertainment'
        WHEN 797 THEN 'Sony Interactive Entertainment'
        WHEN 798 THEN 'Sony Interactive Entertainment'
        WHEN 799 THEN 'Sony Interactive Entertainment'
        WHEN 800 THEN 'Sony Interactive Entertainment'
        WHEN 801 THEN 'Sony Interactive Entertainment'
        WHEN 802 THEN 'Sony Interactive Entertainment'
        WHEN 803 THEN 'Sony Interactive Entertainment'
        WHEN 804 THEN 'Sony Interactive Entertainment'
        WHEN 805 THEN 'Sony Interactive Entertainment'
        WHEN 806 THEN 'Sony Interactive Entertainment'
        WHEN 807 THEN 'Sony Interactive Entertainment'
        WHEN 808 THEN 'Sony Interactive Entertainment'
        WHEN 809 THEN 'Sony Interactive Entertainment'
        WHEN 810 THEN 'Sony Interactive Entertainment'
        WHEN 811 THEN 'Sony Interactive Entertainment'
        WHEN 812 THEN 'Sony Interactive Entertainment'
        WHEN 813 THEN 'Sony Interactive Entertainment'
        WHEN 814 THEN 'Sony Interactive Entertainment'
        WHEN 815 THEN 'Sony Interactive Entertainment'
        WHEN 816 THEN 'Sony Interactive Entertainment'
        WHEN 817 THEN 'Sony Interactive Entertainment'
        WHEN 818 THEN 'Sony Interactive Entertainment'
        WHEN 819 THEN 'Sony Interactive Entertainment'
        WHEN 820 THEN 'Sony Interactive Entertainment'
        WHEN 821 THEN 'Sony Interactive Entertainment'
        WHEN 822 THEN 'Sony Interactive Entertainment'
        WHEN 823 THEN 'Sony Interactive Entertainment'
        WHEN 824 THEN 'Sony Interactive Entertainment'
        WHEN 825 THEN 'Sony Computer Entertainment'
        WHEN 826 THEN 'Sony Computer Entertainment'
        WHEN 827 THEN 'Sony Computer Entertainment'
        WHEN 828 THEN 'Sony Interactive Entertainment'
        WHEN 829 THEN 'Sony Interactive Entertainment'
        WHEN 830 THEN 'Sony Interactive Entertainment'
        WHEN 831 THEN 'Nintendo'
        WHEN 832 THEN 'Nintendo'
        WHEN 833 THEN 'Nintendo'
        WHEN 834 THEN 'Nintendo'
        WHEN 835 THEN 'Nintendo'
        WHEN 836 THEN 'Nintendo'
        WHEN 837 THEN 'Nintendo'
        WHEN 838 THEN 'Nintendo'
        WHEN 839 THEN 'Nintendo'
        WHEN 840 THEN 'Nintendo'
        WHEN 841 THEN 'Nintendo'
        WHEN 842 THEN 'Nintendo'
        WHEN 843 THEN 'Nintendo'
        WHEN 844 THEN 'Nintendo'
        WHEN 845 THEN 'Nintendo'
        WHEN 846 THEN 'Nintendo'
        WHEN 847 THEN 'Nintendo'
        WHEN 848 THEN 'Nintendo'
        WHEN 849 THEN 'Nintendo'
        WHEN 850 THEN 'Nintendo'
        WHEN 851 THEN 'Nintendo'
        WHEN 852 THEN 'Nintendo'
        WHEN 853 THEN 'Nintendo'
        WHEN 854 THEN 'Nintendo'
        WHEN 855 THEN 'Nintendo'
        WHEN 856 THEN 'Nintendo'
        WHEN 857 THEN 'Nintendo'
        WHEN 858 THEN 'Nintendo'
        WHEN 859 THEN 'Nintendo'
        WHEN 860 THEN 'Nintendo'
        WHEN 861 THEN 'Nintendo'
        WHEN 862 THEN 'Nintendo'
        WHEN 863 THEN 'Nintendo'
        WHEN 864 THEN 'Nintendo'
        WHEN 865 THEN 'Nintendo'
        WHEN 866 THEN 'Nintendo'
        WHEN 867 THEN 'Nintendo'
        WHEN 868 THEN 'Nintendo'
        WHEN 869 THEN 'Nintendo'
        WHEN 870 THEN 'Nintendo'
        WHEN 871 THEN 'Nintendo'
        WHEN 872 THEN 'Nintendo'
        WHEN 873 THEN 'Nintendo'
        WHEN 874 THEN 'Nintendo'
        WHEN 875 THEN 'Nintendo'
        WHEN 876 THEN 'Nintendo'
        WHEN 877 THEN 'Nintendo'
        WHEN 878 THEN 'Nintendo'
        WHEN 879 THEN 'Nintendo'
        WHEN 880 THEN 'Nintendo'
        WHEN 881 THEN 'Nintendo'
        WHEN 882 THEN 'Nintendo'
        WHEN 883 THEN 'Nintendo'
        WHEN 884 THEN 'Nintendo'
        WHEN 885 THEN 'Nintendo'
        WHEN 886 THEN 'Nintendo'
        WHEN 887 THEN 'Nintendo'
        WHEN 888 THEN 'Nintendo'
        WHEN 889 THEN 'Nintendo'
        WHEN 890 THEN 'Nintendo'
        WHEN 891 THEN 'Nintendo'
        WHEN 892 THEN 'Nintendo'
        WHEN 893 THEN 'Nintendo'
        WHEN 894 THEN 'Nintendo'
        WHEN 895 THEN 'Nintendo'
        WHEN 896 THEN 'Nintendo'
        WHEN 897 THEN 'Nintendo'
        WHEN 898 THEN 'Nintendo'
        WHEN 899 THEN 'Nintendo'
        WHEN 900 THEN 'Nintendo'
        WHEN 901 THEN 'Nintendo'
        WHEN 902 THEN 'Nintendo'
        WHEN 903 THEN 'Nintendo'
        WHEN 904 THEN 'Nintendo'
        WHEN 905 THEN 'Nintendo'
        WHEN 906 THEN 'Nintendo'
        WHEN 907 THEN 'Nintendo'
        WHEN 908 THEN 'Nintendo'
        WHEN 909 THEN 'Nintendo'
        WHEN 910 THEN 'Nintendo'
        WHEN 911 THEN 'Nintendo'
        WHEN 912 THEN 'Nintendo'
        WHEN 913 THEN 'Nintendo'
        WHEN 914 THEN 'Nintendo'
        WHEN 915 THEN 'Nintendo'
        WHEN 916 THEN 'Nintendo'
        WHEN 917 THEN 'Nintendo'
        WHEN 918 THEN 'Nintendo'
        WHEN 919 THEN 'Nintendo'
        WHEN 920 THEN 'Marvelous'
        WHEN 921 THEN 'Capcom'
        WHEN 922 THEN 'Capcom'
        WHEN 923 THEN 'Sega'
        WHEN 924 THEN 'Sega'
        WHEN 925 THEN 'Atlus'
        WHEN 926 THEN 'Sega'
        WHEN 927 THEN 'Sega'
        WHEN 928 THEN 'Spike Chunsoft'
        WHEN 929 THEN 'Spike Chunsoft'
        WHEN 930 THEN 'Spike Chunsoft'
        WHEN 931 THEN 'Spike Chunsoft'
        WHEN 932 THEN 'Spike Chunsoft'
        WHEN 933 THEN 'Spike Chunsoft'
        WHEN 934 THEN 'Capcom'
        WHEN 935 THEN 'Capcom'
        WHEN 936 THEN 'Capcom'
        WHEN 937 THEN 'Nintendo'
        WHEN 938 THEN 'Nintendo'
        WHEN 939 THEN 'Nintendo'
        WHEN 940 THEN 'Nintendo'
        WHEN 941 THEN 'Square Enix'
        WHEN 942 THEN 'Sega'
        WHEN 943 THEN 'Konami'
        WHEN 944 THEN 'Konami'
        WHEN 945 THEN 'Konami'
        WHEN 946 THEN 'Konami'
        WHEN 947 THEN 'Konami'
        WHEN 948 THEN 'Konami'
        WHEN 949 THEN 'Konami'
        WHEN 950 THEN 'Konami'
        WHEN 951 THEN 'Koei Tecmo'
        WHEN 952 THEN 'Koei Tecmo'
        WHEN 953 THEN 'Koei Tecmo'
        WHEN 954 THEN 'Koei Tecmo'
        WHEN 955 THEN 'THQ Nordic'
        WHEN 956 THEN 'THQ Nordic'
        WHEN 957 THEN 'THQ Nordic'
        WHEN 958 THEN 'THQ Nordic'
        WHEN 959 THEN 'Activision'
        WHEN 960 THEN 'Activision'
        WHEN 961 THEN 'Sony Interactive Entertainment'
        WHEN 962 THEN 'Square Enix'
        WHEN 963 THEN 'Square Enix'
        WHEN 964 THEN 'Deep Silver'
        WHEN 965 THEN 'Deep Silver'
        WHEN 966 THEN 'Deep Silver'
        WHEN 967 THEN 'Deep Silver'
        WHEN 968 THEN 'Deep Silver'
        WHEN 969 THEN 'Deep Silver'
        WHEN 970 THEN 'Square Enix'
        WHEN 971 THEN 'Square Enix'
        WHEN 972 THEN 'Square Enix'
        WHEN 973 THEN 'Square Enix'
        WHEN 974 THEN '2K'
        WHEN 975 THEN '2K'
        WHEN 976 THEN '2K'
        WHEN 977 THEN '2K'
        WHEN 978 THEN '2K'
        WHEN 979 THEN '2K'
        WHEN 980 THEN 'Nacon'
        WHEN 981 THEN 'Nacon'
        WHEN 982 THEN 'Electronic Arts'
        WHEN 983 THEN 'Electronic Arts'
        WHEN 984 THEN 'Xbox Game Studios'
        WHEN 985 THEN 'Xbox Game Studios'
        WHEN 986 THEN 'Xbox Game Studios'
        WHEN 987 THEN 'Xbox Game Studios'
        WHEN 988 THEN 'Xbox Game Studios'
        WHEN 989 THEN 'Ubisoft'
        WHEN 990 THEN 'Ubisoft'
        WHEN 991 THEN 'Ubisoft'
        WHEN 992 THEN 'Ubisoft'
        WHEN 993 THEN 'TaleWorlds Entertainment'
        WHEN 994 THEN 'TaleWorlds Entertainment'
        WHEN 995 THEN 'Lo-Fi Games'
        WHEN 996 THEN 'Ludeon Studios'
        WHEN 997 THEN 'Kitfox Games'
        WHEN 998 THEN 'Wube Software'
        WHEN 999 THEN 'Coffee Stain Publishing'
        WHEN 1000 THEN 'Gamera Game'
        WHEN 1001 THEN 'Klei Entertainment'
        WHEN 1002 THEN '11 bit studios'
        WHEN 1003 THEN '11 bit studios'
        WHEN 1004 THEN 'Shining Rock Software'
        WHEN 1005 THEN 'Hooded Horse'
        WHEN 1006 THEN 'Mechanistry'
        WHEN 1007 THEN 'Hooded Horse'
        WHEN 1008 THEN '3Division'
        WHEN 1009 THEN 'Ubisoft'
        WHEN 1010 THEN 'Ubisoft'
        WHEN 1011 THEN 'Ubisoft'
        WHEN 1012 THEN 'Kalypso Media'
        WHEN 1013 THEN 'Kalypso Media'
        WHEN 1014 THEN 'Kalypso Media'
        WHEN 1015 THEN 'Paradox Interactive'
        WHEN 1016 THEN 'The Indie Stone'
        WHEN 1017 THEN '11 bit studios'
        WHEN 1018 THEN 'Numantian Games'
        WHEN 1019 THEN 'Paradox Interactive'
        WHEN 1020 THEN 'Paradox Interactive'
        WHEN 1021 THEN 'Kasedo Games'
        WHEN 1022 THEN 'Assemble Entertainment'
        WHEN 1023 THEN 'Shiro Games'
        WHEN 1024 THEN 'Toukana Interactive'
        WHEN 1025 THEN 'Dinosaur Polo Club'
        WHEN 1026 THEN 'Dinosaur Polo Club'
        WHEN 1027 THEN 'Square Enix Collective'
        WHEN 1028 THEN 'Frozen District'
        WHEN 1029 THEN 'Frozen District'
        WHEN 1030 THEN 'The Irregular Corporation'
        WHEN 1031 THEN 'The Irregular Corporation'
        WHEN 1032 THEN 'PlayWay'
        WHEN 1033 THEN 'PlayWay'
        WHEN 1034 THEN 'PlayWay'
        WHEN 1035 THEN 'Movie Games'
        WHEN 1036 THEN 'Cocopo'
        WHEN 1037 THEN 'Cocopo'
        WHEN 1038 THEN 'Genesz'
        WHEN 1039 THEN 'Amistech Games'
        WHEN 1040 THEN 'THQ Nordic'
        WHEN 1041 THEN 'Strategy First'
        WHEN 1042 THEN 'Strategy First'
        WHEN 1043 THEN 'Strategy First'
        WHEN 1044 THEN 'Strategy First'
        WHEN 1045 THEN 'Electronic Arts'
        WHEN 1046 THEN 'Electronic Arts'
        WHEN 1047 THEN 'Electronic Arts'
        WHEN 1048 THEN 'Electronic Arts'
        WHEN 1049 THEN 'Electronic Arts'
        WHEN 1050 THEN 'Frontier Developments'
        WHEN 1051 THEN 'Frontier Developments'
        WHEN 1052 THEN 'Frontier Developments'
        WHEN 1053 THEN 'Sega'
        WHEN 1054 THEN 'Sega'
        WHEN 1055 THEN 'Sega'
        WHEN 1056 THEN 'Sega'
        WHEN 1057 THEN 'Paradox Interactive'
        WHEN 1058 THEN 'Paradox Interactive'
        WHEN 1059 THEN 'Paradox Interactive'
        WHEN 1060 THEN 'Paradox Interactive'
        WHEN 1061 THEN 'Paradox Interactive'
        WHEN 1062 THEN 'Paradox Interactive'
        WHEN 1063 THEN 'Paradox Interactive'
        WHEN 1064 THEN 'Focus Home Interactive'
        WHEN 1065 THEN 'Paradox Interactive'
        WHEN 1066 THEN 'Paradox Interactive'
        WHEN 1067 THEN 'Paradox Interactive'
        WHEN 1068 THEN 'Sega'
        WHEN 1069 THEN 'Sega'
        WHEN 1070 THEN 'Sega'
        WHEN 1071 THEN 'Electronic Arts'
        WHEN 1072 THEN 'Electronic Arts'
        WHEN 1073 THEN 'Electronic Arts'
        WHEN 1074 THEN 'Electronic Arts'
        WHEN 1075 THEN 'Electronic Arts'
        WHEN 1076 THEN 'Electronic Arts'
        WHEN 1077 THEN 'Blizzard Entertainment'
        WHEN 1078 THEN 'Blizzard Entertainment'
        WHEN 1079 THEN 'Blizzard Entertainment'
        WHEN 1080 THEN 'Blizzard Entertainment'
        WHEN 1081 THEN 'Blizzard Entertainment'
        WHEN 1082 THEN 'Blizzard Entertainment'
        WHEN 1083 THEN 'Xbox Game Studios'
        WHEN 1084 THEN 'Xbox Game Studios'
        WHEN 1085 THEN 'Xbox Game Studios'
        WHEN 1086 THEN 'Xbox Game Studios'
        WHEN 1087 THEN 'Firefly Studios'
        WHEN 1088 THEN 'Firefly Studios'
        WHEN 1089 THEN 'Firefly Studios'
        WHEN 1090 THEN 'Firefly Studios'
        WHEN 1091 THEN 'Firefly Studios'
        WHEN 1092 THEN 'Firefly Studios'
        WHEN 1093 THEN 'Firefly Studios'
        WHEN 1094 THEN 'Sega'
        WHEN 1095 THEN 'Sega'
        WHEN 1096 THEN '1C Company'
        WHEN 1097 THEN '1C Company'
        WHEN 1098 THEN '1C Company'
        WHEN 1099 THEN '1C Company'
        WHEN 1100 THEN 'Gearbox Publishing'
        ELSE publisher
    END
WHERE id BETWEEN 1 AND 1100;

-- ============================================================
-- Useful search indexes/queries
-- ============================================================

-- Search example:
-- SELECT id, name, slug, source, cover_url, icon_url, artwork_url
-- FROM game_catalog
-- WHERE is_active = 1
--   AND name LIKE CONCAT('%', ?, '%')
-- ORDER BY name ASC
-- LIMIT 20;

-- Recommended relationship:
--   game_catalog.id -> user_games.game_id
--
-- The application uses game_catalog.id as the game_id in user_games.
