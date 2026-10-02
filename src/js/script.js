(() => {
	// 文言（PHP 側で wp_localize_script により翻訳済みの値を渡す）
	const l10n = window.mgnWpblockCopyL10n || {};
	const labels = {
		copy: l10n.copy || "このページのブロック内容をコピー",
		copied: l10n.copied || "コピーしました！",
		failed: l10n.failed || "コピーに失敗しました",
	};

	function init() {
		if (typeof copyContents === "undefined" || !copyContents) {
			return;
		}

		// footerがあればクラスを追加してボタンを配置、なければbodyに配置
		const footerArea = document.querySelector("footer");
		const btnParent = footerArea || document.body;
		if (footerArea) {
			footerArea.classList.add("mpcb-footer");
		}

		//コピーボタンを生成 / ボタンを追加
		const copyBtn = document.createElement("button");
		copyBtn.type = "button";
		copyBtn.classList.add("mpcb-btn");
		copyBtn.setAttribute("id", "mpcb-copy-btn");
		copyBtn.innerText = labels.copy;
		btnParent.appendChild(copyBtn);

		//テキストエリアの親要素を追加
		const copyArea = document.createElement("div");
		copyArea.classList.add("mpcb-text-area");

		//テキストエリアを生成 / .mpcb-text-areaにテキストエリアを追加
		const textarea = document.createElement("textarea");
		textarea.textContent = copyContents;
		copyArea.appendChild(textarea);
		document.body.appendChild(copyArea);

		//コピーボタンをクリックした時
		copyBtn.addEventListener("click", () => {
			btnCopy(copyBtn, textarea);
		});
	}

	// ボタンの文言を一時的に切り替える
	function showResult(btn, success) {
		btn.innerText = success ? labels.copied : labels.failed;
		setTimeout(() => {
			btn.innerText = labels.copy;
		}, 2000);
	}

	// Clipboard API 非対応・非セキュアコンテキスト用のフォールバック
	function legacyCopy(element) {
		element.focus();
		element.setSelectionRange(0, element.value.length);
		try {
			return document.execCommand("copy");
		} catch (e) {
			return false;
		}
	}

	function btnCopy(btn, element) {
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(element.value).then(
				() => {
					showResult(btn, true);
				},
				() => {
					showResult(btn, legacyCopy(element));
				},
			);
			return;
		}
		showResult(btn, legacyCopy(element));
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
