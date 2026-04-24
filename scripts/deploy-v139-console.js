/**
 * VMRA theme v1.3.9 — one-shot DirectAdmin deploy.
 *
 * HOW TO RUN:
 * 1. Log in to https://web3.bigmountainmail.com:2222
 * 2. Open DevTools (Cmd+Option+I) → Console tab
 * 3. Paste this entire file → Enter
 *
 * Pulls files at commit 49eea86 from GitHub raw and POSTs each one to
 * DirectAdmin's upload API. Uses your current DA session cookie.
 * Logs ✅/❌ per file. Should take ~10-15 seconds total.
 */
(async () => {
  const COMMIT = '49eea86';
  const RAW = `https://raw.githubusercontent.com/Rconman99/vmra-site/${COMMIT}`;
  const THEME_DIR = '/domains/temp.nwvintagemodified.com/public_html/wp-content/themes/vmra';

  // Changed in v1.3.7 + v1.3.8 + v1.3.9, relative to wp-theme/vmra/
  const FILES = [
    'functions.php',
    'style.css',
    'front-page.php',
    'page-classifieds.php',
    'assets/css/home.css',
    'inc/seo-schema.php',
    'deploy/htaccess-security-headers.conf',
  ];

  console.log(`%cVMRA v1.3.9 deploy starting…`, 'color:#ffb02e;font-weight:bold');

  let ok = 0, fail = 0;
  for (const relPath of FILES) {
    const parts = relPath.split('/');
    const filename = parts.pop();
    const subdir = parts.length ? '/' + parts.join('/') : '';
    const targetDir = THEME_DIR + subdir;

    try {
      const ghUrl = `${RAW}/wp-theme/vmra/${relPath}`;
      const body = await (await fetch(ghUrl)).text();
      if (body.startsWith('404') || body.length < 10) {
        throw new Error(`GitHub fetch failed for ${relPath}`);
      }

      const file = new File([body], filename, { type: 'application/octet-stream' });
      const fd = new FormData();
      fd.append('files', file);

      const upUrl = '/api/filemanager-actions/upload?dir=' +
        encodeURIComponent(targetDir) + '&overwrite=true';
      const up = await fetch(upUrl, { method: 'POST', body: fd, credentials: 'same-origin' });

      if (up.status === 204 || up.status === 200) {
        console.log(`%c✅ ${relPath}`, 'color:#6cbf00', `→ ${targetDir}/${filename}`);
        ok++;
      } else {
        console.error(`❌ ${relPath} → HTTP ${up.status}`);
        fail++;
      }
    } catch (err) {
      console.error(`❌ ${relPath}`, err.message);
      fail++;
    }
  }

  console.log(`%cDone. ${ok} uploaded, ${fail} failed.`,
    `color:${fail ? '#d11a2a' : '#6cbf00'};font-weight:bold;font-size:14px`);

  if (fail === 0) {
    console.log(`%c→ NEXT STEP: open /public_html/.htaccess in File Manager and paste the security-headers block at the top (above # BEGIN WordPress).`,
      'color:#ffb02e');
    console.log(`%c   The block is at: /domains/temp.nwvintagemodified.com/public_html/wp-content/themes/vmra/deploy/htaccess-security-headers.conf`,
      'color:#ffb02e');
  }
})();
