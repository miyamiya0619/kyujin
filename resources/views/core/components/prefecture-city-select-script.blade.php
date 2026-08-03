{{--
    都道府県セレクトの補助 JS。企業情報・事業所情報・求職者プロフィールの各フォームで使う。

    都道府県を変更した瞬間に、対応する市区町村の一覧を Ajax で取得して
    選択肢を差し替える(`Public\CityController::byPrefecture`)。
--}}
@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prefectureSelect = document.querySelector('[data-prefecture-select]');
            const citySelect = document.getElementById('city_id');
            if (!prefectureSelect || !citySelect) return;

            function setCityOptions(cities) {
                citySelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '指定なし';
                citySelect.appendChild(placeholder);

                cities.forEach(({ id, name }) => {
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = name;
                    citySelect.appendChild(option);
                });
            }

            prefectureSelect.addEventListener('change', async () => {
                if (!prefectureSelect.value) {
                    setCityOptions([]);
                    return;
                }

                const previousOptions = citySelect.innerHTML;
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">読み込み中&hellip;</option>';

                try {
                    const response = await fetch(`/cities/by-prefecture/${prefectureSelect.value}`, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('市区町村の取得に失敗しました');
                    }

                    setCityOptions(await response.json());
                } catch (error) {
                    // 取得に失敗しても入力を止めないよう、元の選択肢に戻す。
                    citySelect.innerHTML = previousOptions;
                } finally {
                    citySelect.disabled = false;
                }
            });
        });
    </script>
@endonce
