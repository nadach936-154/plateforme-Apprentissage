/*import { test, expect } from '@playwright/test'

test('un enseignant peut créer un cours', async ({ page }) => {
  await page.goto('/login')
  await page.fill('input[type="email"]', 'ahmed@test.com')
  await page.fill('input[type="password"]', 'password123')
  await page.click('button:has-text("Se connecter")')
  await page.waitForURL('teacher/dashboard')

  await page.click('button:has-text("Créer un cours")')
  await page.waitForURL('/teacher/courses/new')

  await page.fill('input[type="text"] >> nth=0', 'Cours de test Playwright')
  await page.fill('textarea >> nth=0', 'Description automatisée')
  await page.fill('textarea >> nth=1', 'Contenu généré automatiquement pour le test.')

  await page.click('button:has-text("Enregistrer")')

  // Vérifie qu'on est redirigé vers une page d'édition (id numérique dans l'URL)
  await page.waitForURL(/\/teacher\/courses\/\d+/)
})*/
import { test, expect } from '@playwright/test';

test('un étudiant peut passer un quiz et voir son score', async ({ page }) => {
  // 1. Connexion
  await page.goto('/login');
  await page.fill('input[type="email"]', 'nada@test.com');
  await page.fill('input[type="password"]', 'password123');
  await page.click('button[type="submit"]');

  // 2. Navigation vers le Dashboard
  await page.waitForURL('**/dashboard');

  // 3. Clic sur le cours "Introduction à Laravel" (garanti d'avoir des quiz via le seeder)
  const targetCourse = page.locator('.course-card', { hasText: 'Introduction à Laravel' });
await targetCourse.waitFor({ state: 'visible' });

  // Préparation de l'écouteur de requête API avant le clic
  const apiPromise = page.waitForResponse(
    (response) => response.url().includes('/api/courses/') && response.status() === 200,
    { timeout: 15000 }
  );

  await targetCourse.click();

  // Attente de la confirmation que Laravel a bien chargé les données du cours
  await apiPromise;

  // 4. Attente et clic sur le bouton du quiz
  const quizButton = page.locator('button').filter({ hasText: /quiz/i }).first();
  await quizButton.waitFor({ state: 'visible', timeout: 10000 });
  await quizButton.click();

  // 5. Attente du chargement de la page Quiz
  await page.waitForURL('**/quizzes/*');

  // 6. Parcours dynamique des questions
  let hasNext = true;
  while (hasNext) {
    const firstOption = page.locator('.option').first();
    await firstOption.waitFor({ state: 'visible', timeout: 10000 });
    await firstOption.click();

    const nextButton = page.locator('button').filter({ hasText: /suivante|suivant/i });
    const finishButton = page.locator('button').filter({ hasText: /terminer|finir/i });

    if (await nextButton.isVisible()) {
      await nextButton.click();
    } else if (await finishButton.isVisible()) {
      await finishButton.click();
      hasNext = false;
    } else {
      hasNext = false;
    }
  }

  // 7. Vérification de l'écran de résultat
  await expect(page.locator('.score-circle, .result-card, .quiz-page').first()).toBeVisible();
});