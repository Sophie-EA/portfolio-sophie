document.querySelectorAll(".project").forEach((project) => {
  project.addEventListener("click", () => {
    project.classList.toggle("active");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const pathElement = document.getElementById("myAnimatedPath");
  if (!pathElement) return; // Vérifie que l'élément existe

  let originalD = pathElement.getAttribute("d");
  const pathDataRegex = /([MQTLVHCSAZ])([^MQTLVHCSAZ]*)/gi;
  let parsedPath = [];
  let match;

  // Parse le chemin SVG
  while ((match = pathDataRegex.exec(originalD)) !== null) {
    const command = match[1];
    const coords = match[2].trim().split(/\s+/).filter(Boolean).map(Number);
    parsedPath.push({ command, coords });
  }

  let frame = 0;
  const amplitudeX = 60;
  const frequencyX = 0.01;
  const amplitudeY = 8;
  const frequencyY = 0.006;

  function animatePath() {
    const animatedPath = parsedPath
      .map((segment) => {
        let newCoords = [...segment.coords];
        for (let i = 0; i < newCoords.length; i += 2) {
          const x = newCoords[i];
          const y = newCoords[i + 1] || 0;
          // Applique une oscillation sinusoïdale
          newCoords[i] =
            x + Math.sin(frame * frequencyX + i * 0.5) * amplitudeX;
          newCoords[i + 1] =
            y + Math.sin(frame * frequencyY + i * 0.3) * amplitudeY;
        }
        return segment.command + " " + newCoords.join(" ");
      })
      .join(" ");

    pathElement.setAttribute("d", animatedPath);
    frame += 0.05;
    requestAnimationFrame(animatePath);
  }

  animatePath(); // Lance l'animation
});
