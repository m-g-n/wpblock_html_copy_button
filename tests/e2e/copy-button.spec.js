/**
 * コピーボタンの E2E テスト.
 */
const { test, expect } = require("@wordpress/e2e-test-utils-playwright");

const CONTENT =
	"<!-- wp:paragraph --><p>E2E テスト本文 `${x}`</p><!-- /wp:paragraph -->";

test.describe("コピーボタン", () => {
	let post;

	test.beforeAll(async ({ requestUtils }) => {
		post = await requestUtils.createPost({
			title: "mgn copy button e2e",
			content: CONTENT,
			status: "publish",
		});
	});

	test.afterAll(async ({ requestUtils }) => {
		await requestUtils.deleteAllPosts();
	});

	const copyUrl = () => `/?p=${post.id}&mgn_wpblock_copy=on`;

	test("管理者にはボタンが表示され、本文をクリップボードにコピーできる", async ({
		page,
		context,
	}) => {
		await context.grantPermissions(["clipboard-read", "clipboard-write"]);
		const errors = [];
		page.on("pageerror", (error) => errors.push(error.message));

		await page.goto(copyUrl());

		const button = page.locator("#mpcb-copy-btn");
		await expect(button).toHaveText("このページのブロック内容をコピー");
		await expect(page.locator("footer.mpcb-footer")).toHaveCount(1);

		await button.click();
		await expect(button).toHaveText("コピーしました！");
		expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
			post.content.raw,
		);

		// 2 秒後に元の文言へ戻る
		await expect(button).toHaveText("このページのブロック内容をコピー", {
			timeout: 5000,
		});
		expect(errors).toEqual([]);
	});

	test("パラメータがない場合はボタンを表示しない", async ({ page }) => {
		await page.goto(`/?p=${post.id}`);
		await expect(page.locator("#mpcb-copy-btn")).toHaveCount(0);
	});

	test("未ログインの場合はボタンも本文も出力しない", async ({ browser }) => {
		const context = await browser.newContext({
			storageState: { cookies: [], origins: [] },
		});
		const page = await context.newPage();

		await page.goto(copyUrl());
		await expect(page.locator("#mpcb-copy-btn")).toHaveCount(0);
		expect(await page.content()).not.toContain("copyContents");

		await context.close();
	});

	test("footer がないテーマでは body に配置し、下余白を付ける", async ({
		page,
	}) => {
		await page.addInitScript(() => {
			document.addEventListener("DOMContentLoaded", () => {
				document.querySelectorAll("footer").forEach((el) => el.remove());
			});
		});
		await page.goto(copyUrl());

		const button = page.locator("body > #mpcb-copy-btn");
		await expect(button).toHaveCount(1);
		await expect(page.locator("body")).toHaveClass(/mpcb-no-footer/);
		await expect(page.locator("body")).toHaveCSS("padding-bottom", "45px");
	});

	test("Clipboard API が使えない場合は execCommand で、失敗時は失敗表示", async ({
		page,
	}) => {
		await page.addInitScript(() => {
			Object.defineProperty(navigator, "clipboard", {
				value: undefined,
				configurable: true,
			});
			window.__execResult = true;
			document.execCommand = () => window.__execResult;
		});
		await page.goto(copyUrl());

		const button = page.locator("#mpcb-copy-btn");
		await button.click();
		await expect(button).toHaveText("コピーしました！");

		await expect(button).toHaveText("このページのブロック内容をコピー", {
			timeout: 5000,
		});
		await page.evaluate(() => {
			window.__execResult = false;
		});
		await button.click();
		await expect(button).toHaveText("コピーに失敗しました");
	});
});
