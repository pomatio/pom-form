const canvases = document.querySelectorAll('.signature-canvas');

canvases.forEach(canvas => {
  const ctx = canvas.getContext('2d');

  // Set canvas dimensions to match its natural resolution
  canvas.width = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;

  let isDrawing = false;
  let lastX = 0;
  let lastY = 0;

  canvas.addEventListener('mousedown', function(e) {
    isDrawing = true;
    let rect = canvas.getBoundingClientRect();
    lastX = e.clientX - rect.left;
    lastY = e.clientY - rect.top;
  });

  canvas.addEventListener('mousemove', function(e) {
    if (isDrawing) {
      let rect = canvas.getBoundingClientRect();
      let mouseX = e.clientX - rect.left;
      let mouseY = e.clientY - rect.top;
      drawLine(ctx, lastX, lastY, mouseX, mouseY);
      lastX = mouseX;
      lastY = mouseY;
    }
  });

  canvas.addEventListener('mouseup', function() {
    isDrawing = false;
    saveCanvasData(canvas);
  });

  canvas.addEventListener('mouseout', function() {
    isDrawing = false;
  });
});

function drawLine(ctx, x1, y1, x2, y2) {
  ctx.beginPath();
  ctx.moveTo(x1, y1);
  ctx.lineTo(x2, y2);
  ctx.strokeStyle = '#000';
  ctx.lineWidth = 2;
  ctx.stroke();
  ctx.closePath();
}

const clearButtons = document.querySelectorAll('.signature-canvas-clear');

clearButtons.forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();

    const canvas = this.parentElement.querySelector('canvas');
    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    saveCanvasData(canvas, true);
  });
});

function saveCanvasData(canvas, clear = false) {
  const input = canvas.parentElement.querySelector('.signature-value');
  input.value = clear ? '' : canvas.toDataURL();

  input.dispatchEvent(new Event('change', { bubbles: true }));
}
