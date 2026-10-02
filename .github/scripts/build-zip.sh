#!/usr/bin/env bash
# 配布用 zip を作成する。開発用ファイルを含めないよう、同梱するファイルを明示的に列挙する。
# 事前に npm run build（dist/）と composer install --no-dev（vendor/）を実行しておくこと。
#
# 使い方: .github/scripts/build-zip.sh [出力ファイル名]
set -euo pipefail

cd "$(dirname "$0")/../.."

output="${1:-mgn_wpblock_copy.zip}"

# 同梱するファイル・ディレクトリ（zip のルートに配置する）
files=(
	mgn_wpblock_copy.php
	readme.txt
	App
	dist
	vendor
)
# 任意（存在する場合のみ同梱）
optional=(
	languages
)

for f in "${files[@]}"; do
	if [ ! -e "$f" ]; then
		echo "::error::同梱に必要な $f がありません（npm run build / composer install --no-dev を実行してください）" >&2
		exit 1
	fi
done
for f in "${optional[@]}"; do
	[ -e "$f" ] && files+=("$f")
done

# 開発用パッケージが vendor に混入していないか確認
if [ -d vendor/phpunit ] || [ -d vendor/squizlabs ]; then
	echo "::error::vendor に開発用パッケージが含まれています（composer install --no-dev で再作成してください）" >&2
	exit 1
fi

rm -f "$output"
zip -rq "$output" "${files[@]}" -x '*.DS_Store' -x '*/.git*'
echo "作成: $output"
unzip -l "$output" | tail -n 1
