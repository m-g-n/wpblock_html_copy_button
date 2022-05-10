window.onload = function(){
	if (copyContents) {
		// footerにボタンがあった場合にクラスを追加
		let footerArea = document.getElementsByTagName('footer');
		footerArea[0].classList.add('mpcb-footer');

		//コピーボタンを生成 / footerにボタンを追加
		let copyBtn = document.createElement('button');
		copyBtn.classList.add('mpcb-btn');
		copyBtn.setAttribute('id', 'mpcb-copy-btn');
		copyBtn.innerText = 'このページのブロック内容をコピー';
		footerArea[0].appendChild(copyBtn);

		//テキストエリアの親要素を追加
		let copyArea = document.createElement('div');
		copyArea.classList.add('mpcb-text-area');

		//テキストエリアを生成 / .mpcb-text-areaにテキストエリアを追加
		let textarea = document.createElement('textarea');
		textarea.textContent = copyContents;
		copyArea.appendChild(textarea);
		document.body.appendChild(copyArea);

		//コピーボタンをクリックした時
		copyBtn.onclick = function(){
			btnCopy(textarea);
		};
	}
}
function copySuccess(btn){
	let btnText = btn.innerText;
	btn.innerText = 'コピーしました！';
	setTimeout(function(){
		btn.innerText = btnText;
	}, 2000);
}

function btnCopy(element) {
	element.focus();
	element.setSelectionRange(0, element.value.length);
	let result;
	let copyBtn = document.getElementById('mpcb-copy-btn');
	try {
		result = document.execCommand("copy");
		copySuccess(copyBtn);
	} catch(e) {
		result = false;
	}
	return result;
}