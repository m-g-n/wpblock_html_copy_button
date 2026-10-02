#!/usr/bin/env bash
# プラグインのルートファイルのヘッダー（Version:）からバージョンを取得して標準出力に出す。
# - バージョン形式（数字とドット）でない場合はエラー
# - readme.txt の Stable tag と一致しない場合はエラー（書き換え漏れ防止）
set -euo pipefail

cd "$(dirname "$0")/../.."

header_value() { # $1=ファイル $2=ヘッダー名
	sed -n "s/^[[:space:]]*\*\{0,1\}[[:space:]]*$2:[[:space:]]*//p" "$1" | head -n 1 | tr -d '[:space:]'
}

version=$(header_value mgn_wpblock_copy.php 'Version')
if ! printf '%s' "$version" | grep -Eq '^[0-9]+(\.[0-9]+)*$'; then
	echo "::error file=mgn_wpblock_copy.php::Version ヘッダーからバージョンを取得できません: '$version'" >&2
	exit 1
fi

stable_tag=$(header_value readme.txt 'Stable tag')
if [ "$stable_tag" != "$version" ]; then
	echo "::error file=readme.txt::readme.txt の Stable tag（'$stable_tag'）が mgn_wpblock_copy.php の Version（'$version'）と一致しません" >&2
	exit 1
fi

printf '%s\n' "$version"
