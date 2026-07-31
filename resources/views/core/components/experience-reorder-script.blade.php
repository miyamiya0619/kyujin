{{--
    職務経歴の並び替え JS。「↑」「↓」ボタンを押すと DOM 上で順序を入れ替え、
    全件の ID を並び順どおりに reorder エンドポイントへ送信する。
--}}
@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const list = document.getElementById('experience-list');
            if (!list) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            function submitOrder() {
                const ids = Array.from(list.querySelectorAll('[data-experience-id]'))
                    .map(el => el.dataset.experienceId);

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('seeker.experiences.reorder') }}";

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;
                form.appendChild(csrf);

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'experience_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }

            list.addEventListener('click', (event) => {
                const item = event.target.closest('[data-experience-id]');
                if (!item) return;

                if (event.target.matches('.js-move-up')) {
                    const prev = item.previousElementSibling;
                    if (prev) {
                        list.insertBefore(item, prev);
                        submitOrder();
                    }
                } else if (event.target.matches('.js-move-down')) {
                    const next = item.nextElementSibling;
                    if (next) {
                        list.insertBefore(next, item);
                        submitOrder();
                    }
                }
            });
        });
    </script>
@endonce
