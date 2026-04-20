// public/js/app.js

document.addEventListener('DOMContentLoaded', () => {

  // ─── Toast ──────────────────────────────────────────────────────────────
  window.showToast = function (msg, type = 'success', duration = 3500) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = msg;
    toast.className = `toast toast--${type} show`;
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), duration);
  };

  // ─── Sparkle Overlay ────────────────────────────────────────────────────
  function createSparkleOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'sparkleOverlay';
    overlay.innerHTML = `
      <div class="sparkle-stage">
        <div class="sparkle-ring">
          <span class="spark spark-1">✦</span>
          <span class="spark spark-2">✦</span>
          <span class="spark spark-3">✦</span>
          <span class="spark spark-4">✦</span>
          <span class="spark spark-5">✦</span>
          <span class="spark spark-6">✦</span>
        </div>
        <div class="sparkle-center">
          <span class="spark-core">✦</span>
          <div class="sparkle-label">Saving your entry…</div>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('sparkle-visible'));
    return overlay;
  }

  function removeSparkleOverlay(overlay, success, message) {
    if (!overlay) return;
    const label = overlay.querySelector('.sparkle-label');

    if (success) {
      label.textContent = message || 'Entry saved!';
      overlay.querySelector('.sparkle-stage').classList.add('sparkle-success');
    } else {
      label.textContent = message || 'Nothing to save yet.';
      overlay.querySelector('.sparkle-stage').classList.add('sparkle-error');
    }

    setTimeout(() => {
      overlay.classList.remove('sparkle-visible');
      overlay.classList.add('sparkle-hiding');
      setTimeout(() => overlay.remove(), 600);
    }, 1400);
  }

  // ─── Compile / Save Entry button ────────────────────────────────────────
  const compileBtn = document.getElementById('compileBtn');
  if (!compileBtn) return;

  compileBtn.addEventListener('click', async () => {
    compileBtn.disabled = true;
    const overlay = createSparkleOverlay();

    try {
      const res = await fetch(window.CHAT_CONFIG?.compileUrl || '/journal/compile', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ date: new Date().toISOString().split('T')[0] }),
      });

      const data = await res.json();

      if (data.success) {
        removeSparkleOverlay(overlay, true,
          `Entry saved — ${data.entry.mood_emoji} ${data.entry.mood} day`);
      } else {
        removeSparkleOverlay(overlay, false, data.message || 'Nothing to compile yet.');
      }

    } catch {
      removeSparkleOverlay(overlay, false, 'Could not save. Please try again.');
    } finally {
      setTimeout(() => { compileBtn.disabled = false; }, 2200);
    }
  });

});
