#!/usr/bin/env node

import { chromium } from 'playwright';
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const configPath = join(rootDir, 'scripts/screenshot-demo.config.json');
const templatePath = join(rootDir, 'docs/screenshots.md.template');
const outputDir = join(rootDir, 'docs/screenshots');
const outputMdPath = join(rootDir, 'docs/screenshots.md');

const config = JSON.parse(readFileSync(configPath, 'utf8'));
const baseUrl = process.env.BASE_URL || config.baseUrl || 'http://127.0.0.1:8000';
const loginEmail = process.env.SCREENSHOT_LOGIN_EMAIL;
const loginPassword = process.env.SCREENSHOT_LOGIN_PASSWORD;

if (!existsSync(outputDir)) {
  mkdirSync(outputDir, { recursive: true });
}

let loggedIn = false;

async function doLogin(page) {
  if (!loginEmail || !loginPassword) {
    console.warn('SCREENSHOT_LOGIN_EMAIL / SCREENSHOT_LOGIN_PASSWORD not set; skipping auth-required screenshots.');
    return false;
  }
  const loginUrl = config.login?.url || '/login';
  await page.goto(new URL(loginUrl, baseUrl).href, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', loginEmail);
  await page.fill('input[name="password"]', loginPassword);
  await page.getByRole('button', { name: /log in/i }).click();
  try {
    await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 15000 });
  } catch {
    console.warn('Login may have failed (still on login page after submit). Check SCREENSHOT_LOGIN_EMAIL and SCREENSHOT_LOGIN_PASSWORD.');
    return false;
  }
  if (page.url().includes('/login')) {
    return false;
  }
  await page.waitForLoadState('networkidle');
  loggedIn = true;
  return true;
}

async function captureScreenshot(page, item) {
  const url = new URL(item.url, baseUrl).href;
  await page.goto(url, { waitUntil: 'networkidle', timeout: 15000 });
  await page.waitForTimeout(1500);
  const path = join(outputDir, item.filename);
  await page.screenshot({ path, fullPage: true });
  console.log('Captured:', item.filename);
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();

  const lines = [];
  for (const item of config.screenshots || []) {
    if (item.requiresAuth && !loggedIn) {
      const ok = await doLogin(page);
      if (!ok) continue;
    }
    try {
      await captureScreenshot(page, item);
      lines.push(`### ${item.caption}\n\n![${item.caption}](screenshots/${item.filename})\n`);
    } catch (err) {
      console.error('Failed:', item.filename, err.message);
    }
  }

  await browser.close();

  const template = readFileSync(templatePath, 'utf8');
  const content = template.replace('{{SCREENSHOTS}}', lines.join('\n'));
  writeFileSync(outputMdPath, content, 'utf8');
  console.log('Written:', outputMdPath);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
