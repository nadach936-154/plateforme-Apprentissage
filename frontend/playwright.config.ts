import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e', // ou './tests' selon votre structure
  timeout: 30000,
  use: {
    baseURL: 'http://localhost:5173',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});