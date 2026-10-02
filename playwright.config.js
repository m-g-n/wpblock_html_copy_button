/**
 * E2E テストの設定.
 * @wordpress/scripts の既定設定を元に、テスト用の wp-env 環境（.wp-env.test.json）を使う。
 */
const { defineConfig } = require("@playwright/test");
const baseConfig = require("@wordpress/scripts/config/playwright.config.js");

module.exports = defineConfig({
	...baseConfig,
	testDir: "./tests/e2e",
	webServer: {
		...baseConfig.webServer,
		// 既定は開発環境（.wp-env.json）を起動するため、テスト用環境に差し替える
		command: "npm run env:test -- start",
	},
});
