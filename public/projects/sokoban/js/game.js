(function () {
  "use strict";

  // ==========================================
  // 1. NIVEAUX
  // ==========================================

  const levels = [
    {
      id: "L1",
      name: "Nv 1",
      map: ["     ", "  p  ", "  P  ", "  @  ", "     "],
    },
    {
      id: "L2",
      name: "Nv 2",
      map: ["  # @", " v   ", " # # ", " V   ", "    #"],
    },
    {
      id: "L3",
      name: "Nv 3",
      map: ["     ", " J # ", " #   ", " j  @", "    #"],
    },
    {
      id: "L4",
      name: "Nv 4",
      map: ["     ", " B  #", "  #  ", "@    ", "   #b"],
    },
    {
      id: "L5",
      name: "Nv 5",
      map: ["j#  p", "     ", " #   ", " JP #", " #@  "],
    },
    {
      id: "L6",
      name: "Nv 6",
      map: ["   #v", " B # ", "## # ", "  V  ", " @ b "],
    },
    {
      id: "L7",
      name: "Nv 7",
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
      name: "Nv 8",
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
      name: "Nv 9",
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

  function measureLevel(raw) {
    const rows = raw.length;
    const cols = raw.reduce((m, line) => Math.max(m, line.length), 0);
    return { rows, cols };
  }

  // ==========================================
  // 2. ÉTAT & UTILITAIRES
  // ==========================================

  const boxEls = new Map(); // couleur -> <img>
  const boxAt = new Map(); // "x,y" -> couleur
  const targetForName = new Map(); // couleur -> "x,y" (cible d'origine, optionnel)
  const goalDivAt = new Map(); // "x,y" -> <div class="goal">

  let board = null;
  let goalsEl = null;
  let wallsLayer = null;
  let playerEl = null;
  let backBtn = null;
  let reinit = null;
  let nextBtn = null;
  let msgEl = null;
  let locked = false;

  let currentLevelIndex = 0;
  let data = null; // résultat de parseLevel
  let px = 0,
    py = 0;
  let currentDir = "down";
  const history = [];

  const order = ["pink", "blue", "yellow", "green"];

  function key(x, y) {
    return `${x},${y}`;
  }
  function unkey(k) {
    return k.split(",").map(Number);
  }

  function clamp(v, min, max) {
    return Math.min(max, Math.max(min, v));
  }

  const sprites = {
    up: "/public/projects/sokoban/assets/haut.png",
    down: "/public/projects/sokoban/assets/bas.png",
    left: "/public/projects/sokoban/assets/gauche.png",
    right: "/public/projects/sokoban/assets/droite.png",
  };

  function setPlayerDir(dir) {
    if (playerEl && sprites[dir]) playerEl.src = sprites[dir];
  }

  function place(el, col, row, { visible } = {}) {
    if (!el) return;
    el.style.gridColumnStart = col;
    el.style.gridRowStart = row;
    if (visible !== undefined)
      el.style.visibility = visible ? "visible" : "hidden";
  }

  // ==========================================
  // 3. CAISSES DOM (créées une seule fois)
  // ==========================================

  function initGame() {
    const boxesLayer = document.getElementById("boxes");
    if (!boxesLayer) {
      console.error("Sokoban: #boxes introuvable !");
      return;
    }
    boxesLayer.innerHTML = "";

    const assets = {
      pink: "box-pink.svg",
      yellow: "box-yellow.svg",
      green: "box-green.svg",
      blue: "box-pink.svg", // en attendant la bleu
    };

    for (const [color, filename] of Object.entries(assets)) {
      const img = document.createElement("img");
      img.id = color;
      img.className = "piece box";
      img.alt = `caisse ${color}`;
      img.src = `/public/projects/sokoban/assets/${filename}`;
      img.style.visibility = "hidden";
      // Teinte manuelle si tu n'as pas d'image bleue propre
      if (color === "blue" && filename === "box-pink.svg") {
        img.style.filter = "hue-rotate(210deg) saturate(1.4) brightness(0.95)";
      }
      boxesLayer.appendChild(img);
      boxEls.set(color, img);
    }
  }

  // ==========================================
  // 4. PARSING DU NIVEAU
  // ==========================================

  function parseLevel(raw) {
    const rows = raw.length;
    const cols = raw.reduce((m, line) => Math.max(m, line.length), 0);
    const FLOOR = 0, WALL = 1;
    const terrain = Array.from({ length: rows }, () => Array(cols).fill(FLOOR));

    const walls = new Set();
    const boxes = new Set();
    const goals = [];
    let player = null;

    for (let y = 0; y < rows; y++) {
      const line = raw[y] || "";
      for (let x = 0; x < line.length; x++) {
        const ch = line[x];
        if (!ch || ch === " ") continue;

        switch (ch) {
          case "#":
            walls.add(key(x, y));
            break;
          case "@":
            player = { x, y };
            break;
          case "$": // caisse sans couleur → fallback rose
            boxes.add(`${x},${y},pink`);
            break;
          case "P":
            boxes.add(`${x},${y},pink`);
            break;
          case "B":
            boxes.add(`${x},${y},blue`);
            break;
          case "J":
            boxes.add(`${x},${y},yellow`);
            break;
          case "V":
          case "G":
            boxes.add(`${x},${y},green`);
            break;
          case "o":
            goals.push({ x, y, color: null });
            break;
          case "p":
            goals.push({ x, y, color: "pink" });
            break;
          case "b":
            goals.push({ x, y, color: "blue" });
            break;
          case "j":
            goals.push({ x, y, color: "yellow" });
            break;
          case "v":
            goals.push({ x, y, color: "green" });
            break;
          default:
            break; // sol
        }
      }
    }

    return { rows, cols, walls, boxes, goals, player };
  }

  // ==========================================
  // 5. RENDU
  // ==========================================

  function drawGoals() {
    if (!goalsEl) return;
    goalsEl.innerHTML = "";
    goalDivAt.clear();

    for (const g of data.goals) {
      const div = document.createElement("div");
      div.className = "goal";
      div.style.gridColumnStart = g.x + 1;
      div.style.gridRowStart = g.y + 1;
      if (g.color) {
        div.dataset.color = g.color;
        div.style.setProperty('--goal-bg', goalColorMap[g.color] || g.color);
      }
      goalsEl.appendChild(div);
      goalDivAt.set(key(g.x, g.y), div);
    }
  }

  function drawWalls() {
    if (!wallsLayer) return;
    wallsLayer.innerHTML = "";
    for (const w of data.walls) {
      const [x, y] = unkey(w);
      const div = document.createElement("div");
      div.className = "wall piece";
      div.style.gridColumnStart = x + 1;
      div.style.gridRowStart = y + 1;
      wallsLayer.appendChild(div);
    }
  }

  function fitBoardToScreen() {
    if (!board || !data) return;
    const { rows, cols } = data;
    const wrapper = document.getElementById("sokoban-game-outer");
    const maxW = (wrapper?.clientWidth || window.innerWidth) - 32;
    const maxH = window.innerHeight - 280; // place pour header + boutons
    const cell = Math.max(
      28,
      Math.min(72, Math.floor(Math.min(maxW / cols, maxH / rows))),
    );
    board.style.setProperty("--cell", cell + "px");
    board.style.setProperty("--cols", cols);
    board.style.setProperty("--rows", rows);
  }

  function drawBoxes() {
    // Cacher toutes les caisses par défaut
    for (const [, el] of boxEls) el.style.visibility = "hidden";

    // Afficher celles présentes sur la grille
    for (const [k, color] of boxAt) {
      const el = boxEls.get(color);
      if (!el) continue;
      const [x, y] = unkey(k);
      place(el, x + 1, y + 1, { visible: true });
    }
  }

  function updateGoalColors() {
    for (const [k, div] of goalDivAt) {
      const occupant = boxAt.get(k) || null;
      if (occupant) div.dataset.occupant = occupant;
      else delete div.dataset.occupant;
    }
  }

    const goalColorMap = {
    pink:   '#E58F8F',
    blue:   '#A7D6F9',
    yellow: '#FFE780',
    green:  '#91BC44'
  };

  function drawFloors() {
    const floorsEl = document.getElementById("floors");
    if (!floorsEl || !data) return;
    floorsEl.innerHTML = "";
    for (let y = 0; y < data.rows; y++) {
      for (let x = 0; x < data.cols; x++) {
        const div = document.createElement("div");
        div.className = "floor";
        div.style.gridColumnStart = x + 1;
        div.style.gridRowStart    = y + 1;
        floorsEl.appendChild(div);
      }
    }
  }


  // ==========================================
  // 6. CHARGEMENT & UI
  // ==========================================

  function loadLevel(idx) {
    if (idx < 0 || idx >= levels.length) return;
    currentLevelIndex = idx;

    const raw = levels[idx].map;
    data = parseLevel(raw);

    // Reset état
    history.length = 0;
    if (backBtn) backBtn.disabled = true;
    if (nextBtn) nextBtn.hidden = true;
    if (msgEl) {
      msgEl.setAttribute('hidden', '');
      msgEl.textContent = "";
    }
    locked = false;

    // Sol & Murs
    drawFloors();
    drawWalls();

    // Joueur
    px = data.player?.x ?? 0;
    py = data.player?.y ?? 0;
    currentDir = "down";
    if (playerEl) {
      setPlayerDir("down");
      place(playerEl, px + 1, py + 1, { visible: true });
    }

    // Cibles & caisses
    boxAt.clear();
    for (const b of data.boxes) {
      const [sx, sy, color] = b.split(",");
      boxAt.set(key(parseInt(sx), parseInt(sy)), color);
    }
    drawGoals();
    drawBoxes();
    updateGoalColors();
    fitBoardToScreen();

    // UI
    const numEl = document.getElementById("current-level-num");
    const nameEl = document.getElementById("current-level-name");
    if (numEl) numEl.textContent = idx + 1;
    if (nameEl) nameEl.textContent = levels[idx].name;

    console.log(
      `Sokoban: niveau "${levels[idx].name}" chargé (${data.cols}x${data.rows})`,
    );
  }

  // ==========================================
  // 7. DÉPLACEMENTS & HISTORIQUE
  // ==========================================

  function tryMove(dx, dy) {
    if (locked) return;

    const oldDir = currentDir;
    if (dx === 0 && dy === -1) currentDir = "up";
    if (dx === 0 && dy === 1) currentDir = "down";
    if (dx === -1 && dy === 0) currentDir = "left";
    if (dx === 1 && dy === 0) currentDir = "right";
    setPlayerDir(currentDir);

    const nx = px + dx;
    const ny = py + dy;

    // Hors limites
    if (nx < 0 || nx >= data.cols || ny < 0 || ny >= data.rows) {
      return false;
    }

    // Mur
    if (data.walls.has(key(nx, ny))) {
      return false;
    }

    // Caisse devant
    const kNext = key(nx, ny);
    if (boxAt.has(kNext)) {
      const ax = nx + dx;
      const ay = ny + dy;
      const kAfter = key(ax, ay);

      // Vérifier après la caisse
      if (ax < 0 || ax >= data.cols || ay < 0 || ay >= data.rows) return false;
      if (data.walls.has(kAfter)) return false;
      if (boxAt.has(kAfter)) return false;

      const color = boxAt.get(kNext);
      boxAt.delete(kNext);
      boxAt.set(kAfter, color);

      history.push({
        type: "push",
        pFrom: [px, py],
        dirPrev: oldDir,
        bFrom: [nx, ny],
        bTo: [ax, ay],
        name: color,
      });
    } else {
      history.push({
        type: "move",
        pFrom: [px, py],
        dirPrev: oldDir,
      });
    }

    // Bouger joueur
    px = nx;
    py = ny;
    place(playerEl, px + 1, py + 1);

    if (backBtn) backBtn.disabled = false;

    drawBoxes();
    updateGoalColors();
    checkWin();
    return true;
  }

  function handleKey(e) {
    let dx = 0,
      dy = 0;
    switch (e.key) {
      case "ArrowUp":
      case "z":
      case "Z":
        dy = -1;
        break;
      case "ArrowDown":
      case "s":
      case "S":
        dy = 1;
        break;
      case "ArrowLeft":
      case "q":
      case "Q":
        dx = -1;
        break;
      case "ArrowRight":
      case "d":
      case "D":
        dx = 1;
        break;
      default:
        return;
    }
    e.preventDefault();
    tryMove(dx, dy);
  }

  // ==========================================
  // 8. VICTOIRE
  // ==========================================
  function checkWin() {
    if (!data || !data.goals.length) return false;

    for (const g of data.goals) {
      const k = key(g.x, g.y);
      const occupant = boxAt.get(k);   // "pink", "blue", "yellow", "green"
      const expected = g.color;        // null ou "pink", "blue"...

      if (!occupant) return false;     // une cible encore vide

      // Si la cible attend une couleur précise, la caisse doit correspondre
      if (expected && occupant !== expected) return false;
    }

    // === VICTOIRE ===
    if (msgEl) {
      msgEl.textContent = 'Niveau réussi ! 🎉';
      msgEl.removeAttribute('hidden');
    }
    if (nextBtn) {
      if (currentLevelIndex < levels.length - 1) {
        nextBtn.hidden = false;  // montre "Suivant" si ce n'est pas le dernier
      } 
      else {
        nextBtn.hidden = true;   // cache "Suivant" sur le dernier niveau
        // et tu peux ajouter dans le msg :
        msgEl.textContent = "🎉 Niveau final réussi ! Clique sur Recommencer pour rejouer.";
        setTimeout(() => {
          loadLevel(0);
        }, 2000);
      }
    return true;
    }
  }

  // ==========================================
  // 9. UNDO / NAVIGATION / RESIZE
  // ==========================================

  function undo() {
    if (locked) locked = false;
    if (!history.length) return;

    const step = history.pop();

    if (step.type === "move") {
      px = step.pFrom[0];
      py = step.pFrom[1];
      currentDir = step.dirPrev;
      setPlayerDir(currentDir);
      place(playerEl, px + 1, py + 1);
    } else if (step.type === "push") {
      const kFrom = key(step.bFrom[0], step.bFrom[1]);
      const kTo = key(step.bTo[0], step.bTo[1]);
      const color = step.name;

      boxAt.delete(kTo);
      boxAt.set(kFrom, color);

      const el = boxEls.get(color);
      if (el)
        place(el, step.bFrom[0] + 1, step.bFrom[1] + 1, { visible: true });

      px = step.pFrom[0];
      py = step.pFrom[1];
      currentDir = step.dirPrev;
      setPlayerDir(currentDir);
      place(playerEl, px + 1, py + 1);
    }

    if (!history.length && backBtn) backBtn.disabled = true;
    if (nextBtn) nextBtn.hidden = false;
    if (msgEl) {
      msgEl.hidden = true;
      msgEl.classList.remove("overlay");
      msgEl.textContent = "";
    }

    drawBoxes();
    updateGoalColors();
  }

  function nextLevel() {
    const next = currentLevelIndex + 1;
    if (next < levels.length) loadLevel(next);
    else {
      alert("Bravo ! Tous les niveaux sont terminés.");
      currentLevelIndex = "";
    }
  }

  function reinitGame() {
    loadLevel(currentLevelIndex);
    history.length = 0;
    if (backBtn) backBtn.disabled = true;
  }

  function handleResize() {
    fitBoardToScreen();
  }

  // ==========================================
  // 10. MOBILE
  // ==========================================

  function bindDirectionButtons() {
    const dirs = [
      { id: "btn-up", dx: 0, dy: -1 },
      { id: "btn-down", dx: 0, dy: 1 },
      { id: "btn-left", dx: -1, dy: 0 },
      { id: "btn-right", dx: 1, dy: 0 },
    ];

    dirs.forEach(({ id, dx, dy }) => {
      const btn = document.getElementById(id);
      if (btn) {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          tryMove(dx, dy);
        });
      }
    });
  }

  // ==========================================
  // 11. INITIALISATION
  // ==========================================

  function init() {
    board = document.getElementById("board");
    if (!board) {
      console.log("Sokoban: #board absent — initialisation annulée.");
      return;
    }

    goalsEl = document.getElementById("goals");
    wallsLayer = document.getElementById("walls");
    playerEl = document.getElementById("player");
    backBtn = document.getElementById("back");
    reinit = document.getElementById("reinit");
    nextBtn = document.getElementById("next");
    msgEl = document.getElementById("msg");
    nextBtn.hidden = true; 
    initGame();

    if (backBtn) backBtn.addEventListener("click", undo);
    if (reinit) reinit.addEventListener("click", reinitGame);
    if (nextBtn) nextBtn.addEventListener("click", nextLevel);
    document.addEventListener("keydown", handleKey);
    window.addEventListener("resize", handleResize);

    bindDirectionButtons();

    loadLevel(0);
    console.log("Sokoban initialisé avec succès");
  }

  // Lancement
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
