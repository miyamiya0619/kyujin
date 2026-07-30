{{--
    都道府県セレクトの補助 JS。企業情報・事業所情報の両フォームで使う。

    **市区町村の動的読み込みは今回のスコープ外。**
    都道府県を変更すると本来は Ajax で該当の市区町村を取得すべきだが、
    そのためだけに API エンドポイントを増やすのは T-06 の範囲を超える。
    ここでは都道府県を変更したときに市区町村の選択肢を一旦リセットし、
    保存後の再読み込みで正しい候補に更新される、という割り切りにしている。
    体験を改善する場合は Phase 2 で軽量な API を足す形で対応する。
--}}
@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prefectureSelect = document.querySelector('[data-prefecture-select]');
            const citySelect = document.getElementById('city_id');
            if (!prefectureSelect || !citySelect) return;

            // 初期表示時点の市区町村選択肢を都道府県 ID ごとに記憶しておく。
            // サーバ側で既に「選ばれた都道府県の市区町村」だけを渡しているため、
            // ここでは「都道府県を変更したら選択肢をリセットする」だけを行う。
            const initialPrefectureId = prefectureSelect.value;

            prefectureSelect.addEventListener('change', () => {
                if (prefectureSelect.value !== initialPrefectureId) {
                    citySelect.innerHTML = '<option value="">都道府県を選び直すと候補が更新されます(保存後に反映)</option>';
                    citySelect.disabled = true;
                } else {
                    citySelect.disabled = false;
                }
            });
        });
    </script>
@endonce
