// /public/projects/sokoban/js/game.js

(function() {
  'use strict';

  // ==========================================
  // DONNÉES ET VARIABLES GLOBALES (internes au module)
  // ==========================================
  
  const levels = [
    { id: "L1", name: "Nv1", map: ["     ", "  b  ", "  $B ", "  @  ", "     "] },
    { id: "L2", name: "Nv2", map: ["  # @", " v   ", " # # ", " V   ", "    #"] },
    { id: "L3", name: "Nv3", map: ["     ", " J # ", " #   ", " j  @", "    #"] },
    { id: "L4", name: "Nv4", map: ["     ", " B  #", "  #  ", "@    ", "   #b"] },
    { id: "L5", name: "Nv5", map: ["j#  p", "     ", " #   ", " JP #", " #@  "] },
    { id: "L6", name: "Nv6", map: ["   #v", " B # ", "## # ", "  V  ", " @ b "] },
    {
      id: "L7",
      name: "Nv7",
      map: [
        "   #   ",
        "   #B  ",
        "   p   ",
        "  ###  ",
        "   b   ",
        "  P#   ",
        " @ #   ",
      ],
    },
    {
      id: "L8",
      name: "Nv8",
      map: [
        "#   #",
        "# J  ",
        " p#  ",
        " @j  ",
        " ##  ",
        " P   ",
        "  #  ",
        "#   #",
      ],
    },
    {
      id: "L9",
      name: "Nv9",
      map: [
        "##########",
        "#   #    #",
        "# @P#p#J #",
        "#   j #  #",
        "# vb# #  #",
        "#   V B  #",
        "#        #",
        "##########",
      ],
    },
  ];

  //------identifie le nombre de lignes et colonnes---------------------------
  function measureLevel(raw) {
    const rows = raw.length;
    const cols = raw.reduce((m, line) => Math.max(m, line.length), 0);
    return { rows, cols };
  }

  //------------ Map des caisses (images DOM) --------------------------------
  const boxEls = new Map();
  
  // --- Utils clés -----------------------------------------------------------
  function key(x, y) {
    return `${x},${y}`;
  }
  function unkey(k) {
    return k.split(",").map(Number);
  }
  function sortByYX(keys) {
    return [...keys].sort((a, b) => {
      const [ax, ay] = unkey(a),
        [bx, by] = unkey(b);
      return ay - by || ax - bx;
    });
  }

  // --- Identité & cibles ----------------------------------------------------
  const order = ["pink", "blue", "yellow", "green"]; // ordre canonique
  const boxAt = new Map(); // posKey -> name
  const boxElements = new Map();
  const targetForName = new Map(); // name   -> posKey
  const goalDivAt = new Map(); // posKey -> HTMLElement

  // --- Helpers DOM ----------------------------------------------------------
  let board = null;
  let goalsEl = null;
  let wallsLayer = null;
  let playerEl = null;
  let backBtn = null;
  let reinit = null;
  let nextBtn = null;
  let msgEl = null;
  let locked = false;

  // Historique LIFO des actions effectuées
  const history = [];

  // Sprites joueur
  const sprites = {
    up: "./img/haut.png",
    down: "./img/bas.png",
    left: "./img/gauche.png",
    right: "./img/droite.png",
  };
  function setPlayerDir(dir) {
    if (playerEl) playerEl.src = sprites[dir];
  }

  // --------------------------- State global ---------------------------------
  let currentLevelIndex = 0; // L1 = 0, L2 = 1, ...
  let data; // état du niveau courant (parseLevel)
  let px = 0, py = 0; // position joueur (cache locale)
  let currentDir = "right";

  // --- Parsing niveau -------------------------------------------------------
  function parseLevel(raw) {
    // Protection si raw est undefined ou vide
    if (!raw || !Array.isArray(raw) || raw.length === 0) {
      console.error("Niveau vide ou invalide");
      return {
        rows: 0,
        cols: 0,
        terrain: [],
        walls: new Set(),
        boxes: new Set(),
        goals: [],
        player: null,
      };
    }

    const rows = raw.length;
    const cols = Math.max(...raw.map((line) => line?.length || 0));

    const FLOOR = 0,
      WALL = 1,
      GOAL = 2;
    const terrain = Array.from({ length: rows }, () => Array(cols).fill(FLOOR));
    const walls = new Set();
    const boxes = new Set(); // Set de "x,y,color" ou "x,y"
    const goals = []; // {x, y, color?}
    let player = null;

    for (let y = 0; y < rows; y++) {
      const line = raw[y] || "";
      for (let x = 0; x < line.length; x++) {
        const ch = line[x];

        // Si espace ou undefined, c'est du sol
        if (!ch || ch === " ") continue;

        switch (ch) {
          case "#":
            terrain[y][x] = WALL;
            walls.add(key(x, y));
            break;

          case "@":
            player = { x, y };
            break;

          case "$":
            terrain[y][x] = FLOOR;
            boxes.add(`${x},${y}`); // Caisse sans couleur (auto)
            break;

          // CAISSES COLORÉES (majuscules)
          case "P":
            terrain[y][x] = FLOOR;
            boxes.add(`${x},${y},pink`);
            break;
          case "B":
            terrain[y][x] = FLOOR;
            boxes.add(`${x},${y},blue`);
            break;
          case "J":
            terrain[y][x] = FLOOR;
            boxes.add(`${x},${y},yellow`);
            break;
          case "V":
          case "G": // alternative pour green si tu préfères
            terrain[y][x] = FLOOR;
            boxes.add(`${x},${y},green`);
            break;

          // CIBLES (minuscules)
          case "o":
            terrain[y][x] = GOAL;
            goals.push({ x, y, color: null }); // Cible auto
            break;
          case "p":
            terrain[y][x] = GOAL;
            goals.push({ x, y, color: "pink" });
            break;
          case "b":
            terrain[y][x] = GOAL;
            goals.push({ x, y, color: "blue" });
            break;
          case "j":
            terrain[y][x] = GOAL;
            goals.push({ x, y, color: "yellow" });
            break;
          case "v":
            terrain[y][x] = GOAL;
            goals.push({ x, y, color: "green" });
            break;

          default:
            // Caractère inconnu = sol
            break;
        }
      }
    }

    // Vérification cruciale
    if (!player) {
      console.warn("Pas de joueur (@) trouvé dans le niveau !");
    }

    return {
      rows,
      cols,
      terrain,
      walls,
      boxes, // Contient les strings avec ou sans couleur
      goals,
      player,
    };
  }

  // --- Appariement caisses ↔ goals -----------------------------------------
  function setupBijectionBoxesGoals() {
    // Reset
    boxAt.clear();
    targetForName.clear();

    // Remplit boxAt : position → couleur
    for (const boxStr of data.boxes) {
      const parts = boxStr.split(",");
      const x = parseInt(parts[0]);
      const y = parseInt(parts[1]);
      const color = parts[2] || "auto"; // pink, blue, yellow, green ou auto

      boxAt.set(key(x, y), color);
    }

    // Pour checkWin : couleur → position cible (prend la 1ère dispo si plusieurs)
    const usedGoals = new Set();
    for (const goal of data.goals) {
      const k = key(goal.x, goal.y);
      if (goal.color && !usedGoals.has(goal.color)) {
        targetForName.set(goal.color, k);
        usedGoals.add(goal.color);
      } else if (!goal.color && !usedGoals.has("auto")) {
        targetForName.set("auto", k);
        usedGoals.add("auto");
      }
    }
  }

  // --- Dessin des cibles (colorées par data-*) ------------------------------
  function drawGoals() {
    if (!goalsEl) return;
    goalsEl.innerHTML = "";
    goalDivAt.clear();

    for (const { x, y } of data.goals) {
      const pos = key(x, y);
      const div = document.createElement("div");
      div.className = "goal";
      div.style.gridColumnStart = x + 1;
      div.style.gridRowStart = y + 1;

      const entry = [...targetForName.entries()].find(([, g]) => g === pos);
      if (entry) div.dataset.goal = entry[0];

      goalsEl.appendChild(div);
      goalDivAt.set(pos, div);
    }
    updateGoalColors();
  }

  // --- MAJ couleurs (occupant bon/mauvais) ---------------------------------
  function updateGoalColors() {
    for (const [posKey, div] of goalDivAt) {
      const occ = boxAt.get(posKey) || "";
      if (occ) div.dataset.occupant = occ;
      else div.removeAttribute("data-occupant");
    }
  }

  // ==================== DIMENSIONNEMENT ====================

  function fitBoardToScreen() {
    if (!data || !data.rows || !data.cols) return;

    const margin = 60; // marge pour les boutons en bas

    // Espace disponible
    const availableHeight = window.innerHeight - margin;
    const availableWidth = window.innerWidth - 40; // marge latérale

    // Taille max par cellule
    const cellByHeight = Math.floor(availableHeight / data.rows);
    const cellByWidth = Math.floor(availableWidth / data.cols);

    // Prendre le plus petit pour que ça rentre, limité entre 30px et 100px
    const cellSize = Math.max(30, Math.min(cellByHeight, cellByWidth, 100));

    board.style.setProperty("--cell", cellSize + "px");

    // Recentrer le board si besoin
    board.style.margin = "20px auto";
  }

  // --- Placement d'une pièce sur la grille (1..COLS / 1..ROWS) -------------
  function place(el, col, row, { visible } = {}) {
    if (!el) return;
    el.style.gridColumnStart = col;
    el.style.gridRowStart = row;
    if (visible !== undefined)
      el.style.visibility = visible ? "visible" : "hidden";
  }

  //------Placement des murs --------------------------------------------------
  function drawWalls() {
    if (!wallsLayer) return;
    wallsLayer.innerHTML = "";
    for (const k of data.walls) {
      const [x, y] = unkey(k);
      const img = document.createElement("img");
      img.src = "./img/mur.png";
      img.alt = "mur";
      img.className = "piece wall";
      wallsLayer.appendChild(img);
      place(img, x + 1, y + 1, { visible: true });
    }
  }

  // --------------------------- Utilitaires ----------------------------------
  function clamp(v, min, max) {
    return Math.min(max, Math.max(min, v));
  }
  function isBoxOnGoal(x, y) {
    return data.terrain[y][x] === 2; // GOAL = 2
  }

  // Test de victoire: chaque caisse sur "sa" cible
  function checkWin() {
    // Vérifie que chaque couleur assignée est sur sa cible
    for (const [color, targetPos] of targetForName) {
      // Quelle couleur est sur cette cible ?
      let colorOnTarget = null;
      for (const [pos, col] of boxAt) {
        if (pos === targetPos) {
          colorOnTarget = col;
          break;
        }
      }

      if (colorOnTarget !== color) {
        return; // Pas encore gagné
      }
    }

    // Victoire !
    locked = true;
    if (nextBtn) nextBtn.hidden = false;
    if (msgEl) {
      msgEl.textContent = "🎉 Niveau réussi !";
      msgEl.hidden = false;
      msgEl.classList.add("overlay");
    }
  }

  // --- Affichage des caisses -----------------------------------
  function drawBoxes() {
    // 1. Cacher toutes les caisses d'abord
    ["pink", "yellow", "blue", "green"].forEach((color) => {
      const el = document.getElementById(color);
      if (el) place(el, 1, 1, { visible: false });
    });

    // 2. Placer les caisses actives selon boxAt
    for (const [posKey, color] of boxAt) {
      const [x, y] = posKey.split(",").map(Number);
      const el = document.getElementById(color);
      if (el) {
        place(el, x + 1, y + 1, { visible: true });
      }
    }
  }

  // --------------------------- Chargement niveau ----------------------------
  function loadLevel(index) {
    currentLevelIndex = index;
    const raw = levels[index]?.map;
    
    if (!raw) {
      console.error("Niveau inconnu:", index);
      return;
    }
    
    const parsed = parseLevel(raw);
    data = parsed; // <- IMPORTANT: data devient l'état courant

    // Vérification
    if (!data.player) {
      console.error("Pas de position joueur (@) dans ce niveau");
      return;
    }

    // Dimensionner la grille
    board.style.setProperty("--cols", parsed.cols);
    board.style.setProperty("--rows", parsed.rows);

    // Reset UI
    history.length = 0;
    if (backBtn) backBtn.disabled = true;
    locked = false;
    if (nextBtn) nextBtn.hidden = true;
    if (msgEl) {
      msgEl.hidden = true;
      msgEl.classList.remove("overlay");
      msgEl.textContent = "";
    }

    // Bijection caisses ↔ goals (couleurs)
    setupBijectionBoxesGoals();

    // Couches statiques
    drawWalls();
    drawGoals();

    // Joueur
    setPlayerDir("right");
    currentDir = "right";
    px = data.player.x;
    py = data.player.y;
    place(playerEl, px + 1, py + 1, { visible: true });

    // Caisses + couleurs
    drawBoxes();
    updateGoalColors();
    
    // Affichage du niveau
    const levelNumEl = document.getElementById("current-level-num");
    const levelNameEl = document.getElementById("current-level-name");
    if (levelNumEl) levelNumEl.textContent = index + 1;
    if (levelNameEl) levelNameEl.textContent = levels[index].name;
    
    fitBoardToScreen();
  }

  // --------------------------- Déplacements ---------------------------------
  function handleKey(e) {
    if (locked) return;
    const move = {
      ArrowLeft: [-1, 0],
      ArrowRight: [1, 0],
      ArrowUp: [0, -1],
      ArrowDown: [0, 1],
    }[e.key];
    if (!move) return;
    e.preventDefault();
    tryMove(move[0], move[1]);
  }

  function dirFromDelta(dx, dy) {
    if (dx === 1) return "right";
    if (dx === -1) return "left";
    if (dy === 1) return "down";
    return "up";
  }

  function getDirName(dx, dy) {
    if (dx === 1) return "right";
    if (dx === -1) return "left";
    if (dy === 1) return "down";
    if (dy === -1) return "up";
    return currentDir; // fallback
  }

  function tryMove(dx, dy) {
    const nx = px + dx;
    const ny = py + dy;
    const k = key(nx, ny);

    // CORRECTION : utiliser data.cols et data.rows, pas data.width/height
    const W = data.cols || 20;
    const H = data.rows || 20;
    
    if (nx < 0 || nx >= W || ny < 0 || ny >= H) return;

    // Mur ?
    if (data.walls.has(k)) return;

    // Caisse ?
    if (boxAt.has(k)) {
      const color = boxAt.get(k);
      const nextX = nx + dx;
      const nextY = ny + dy;
      const nextK = key(nextX, nextY);
      
      // Limites pour la caisse aussi (elle ne doit pas sortir)
      if (nextX < 0 || nextX >= data.cols || nextY < 0 || nextY >= data.rows) return;
      
      // Collision mur/caisse derrière ?
      if (data.walls.has(nextK) || boxAt.has(nextK)) return;

      // Vérifier si la caisse est sur SA cible finale (bloquée)
      const targetPos = targetForName ? targetForName.get(color) : null;
      if (targetPos && k === targetPos) return; // Bloquée sur sa cible

      // Déplace la caisse (logique + visuel)
      boxAt.delete(k);
      boxAt.set(nextK, color);

      const el = document.getElementById(color);
      if (el) place(el, nextX + 1, nextY + 1);

      // Historique
      history.push({
        type: "push",
        pFrom: [px, py],
        bFrom: [nx, ny],
        bTo: [nextX, nextY],
        name: color,
        dirPrev: currentDir,
      });

      updateGoalColors();
    } else {
      // Simple déplacement
      history.push({
        type: "move",
        pFrom: [px, py],
        dirPrev: currentDir,
      });
    }

    // Déplace joueur
    px = nx;
    py = ny;
    place(playerEl, px + 1, py + 1);
    setPlayerDir(getDirName(dx, dy));
    if (backBtn) backBtn.disabled = false;

    checkWin();
  }

  // --------------------------- Undo -----------------------------------------
  function undo() {
    if (locked) locked = false;
    if (!history.length) return;

    const step = history.pop();
    switch (step.type) {
      case "move": {
        // CORRECTION : step.pFrom, pas step.from
        px = step.pFrom[0];
        py = step.pFrom[1];
        setPlayerDir(step.dirPrev);
        currentDir = step.dirPrev;
        place(playerEl, px + 1, py + 1);
        break;
      }
      case "push": {
        const kFrom = key(step.bFrom[0], step.bFrom[1]);
        const kTo = key(step.bTo[0], step.bTo[1]);
        const color = step.name;

        // Logique
        boxAt.delete(kTo);
        boxAt.set(kFrom, color);

        // Visuel : remettre le SVG à sa place
        const el = document.getElementById(color);
        place(el, step.bFrom[0] + 1, step.bFrom[1] + 1);

        // Remet joueur
        px = step.pFrom[0];
        py = step.pFrom[1];
        setPlayerDir(step.dirPrev);
        place(playerEl, px + 1, py + 1);

        updateGoalColors();
        break;
      }
    }

    if (nextBtn) nextBtn.hidden = true;
    if (msgEl) {
      msgEl.hidden = true;
      msgEl.classList.remove("overlay");
      msgEl.textContent = "";
    }

    if (backBtn) backBtn.disabled = history.length === 0;
    checkWin();
  }

  // --------------------------- Navigation -----------------------------------
  function nextLevel() {
    const next = currentLevelIndex + 1;
    if (next < levels.length) loadLevel(next);
    else alert("Bravo ! Tous les niveaux sont terminés.");
  }
  
  function reinitGame() {
    loadLevel(0);
    history.length = 0;
    if (backBtn) backBtn.disabled = true;
  }

  function handleResize() {
    drawGoals();
    fitBoardToScreen();
  }

  // ==========================================
  // INITIALISATION (ne s'exécute que si on est sur la bonne page)
  // ==========================================
  
  function init() {
    // Vérification : est-on sur la page Sokoban ?
    board = document.getElementById("board");
    if (!board) {
      console.log("Sokoban: #board non trouvé, initialisation annulée");
      return;
    }

    // Récupération des éléments DOM
    goalsEl = document.getElementById("goals");
    wallsLayer = document.getElementById("walls");
    playerEl = document.getElementById("player");
    backBtn = document.getElementById("back");
    reinit = document.getElementById("reinit");
    nextBtn = document.getElementById("next");
    msgEl = document.getElementById("msg");

    // Initialisation des références aux caisses
    boxEls.set("pink", document.getElementById("pink"));
    boxEls.set("blue", document.getElementById("blue"));
    boxEls.set("yellow", document.getElementById("yellow"));
    boxEls.set("green", document.getElementById("green"));

    // Event listeners (avec vérifications)
    if (backBtn) backBtn.addEventListener("click", undo);
    if (nextBtn) nextBtn.addEventListener("click", nextLevel);
    if (reinit) reinit.addEventListener("click", reinitGame);
    
    document.addEventListener("keydown", handleKey);
    window.addEventListener("resize", handleResize);

    // Démarrage
    loadLevel(0);
    console.log("Sokoban initialisé avec succès");
  }

  // Lancement conditionnel
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
