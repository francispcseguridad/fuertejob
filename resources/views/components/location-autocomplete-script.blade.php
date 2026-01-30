@props(['citySelector', 'countrySelector', 'provinceSelector' => null, 'islandSelector' => null])

@once
    <style>
        .location-autocomplete-wrapper {
            position: relative;
            width: 95% !important;
        }

        .location-autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1200;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        }

        .location-autocomplete-results.d-none {
            display: none;
        }

        .location-autocomplete-item {
            padding: 0.5rem 0.95rem;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f5;
            transition: background-color 0.15s ease;
            font-size: 0.95rem;
        }

        .location-autocomplete-item:last-child {
            border-bottom: none;
        }

        .location-autocomplete-item:hover {
            background-color: #edf2ff;
        }
    </style>
    <script>
        window.__LOCATIONIQ_KEY = 'pk.d52886ad23ebf6a01e455bb91b89bcc1';
        window.initLocationAutocomplete = function(config) {
            const cityInput = document.querySelector(config.citySelector);
            if (!cityInput) {
                return;
            }

            const countryInput = config.countrySelector ? document.querySelector(config.countrySelector) : null;
            const provinceInput = config.provinceSelector ? document.querySelector(config.provinceSelector) : null;
            const islandInput = config.islandSelector ? document.querySelector(config.islandSelector) : null;
            const LOCATIONIQ_KEY = window.__LOCATIONIQ_KEY;
            const citySelectionMessage = 'Selecciona una ciudad de las sugerencias para completar país y provincia.';

            let resultsContainer = document.createElement('div');
            resultsContainer.className = 'location-autocomplete-results d-none';

            let wrapper = document.createElement('div');
            wrapper.className = 'location-autocomplete-wrapper';

            cityInput.parentNode.insertBefore(wrapper, cityInput);
            wrapper.appendChild(cityInput);
            wrapper.appendChild(resultsContainer);

            let timeout = null;
            let citySelected = Boolean(countryInput && countryInput.value.trim());

            const renderSuggestions = (items) => {
                resultsContainer.innerHTML = '';
                const uniqueKeys = new Set();

                items.forEach(item => {
                    if (!item.city) return;
                    const key =
                        `${item.city}|${item.province || ''}|${item.island || ''}|${item.country || ''}`;
                    if (uniqueKeys.has(key)) return;
                    uniqueKeys.add(key);

                    const div = document.createElement('div');
                    div.className = 'location-autocomplete-item';
                    let displayText = `<strong>${item.city}</strong>`;
                    if (item.province) {
                        displayText += `, <small class="text-muted">${item.province}</small>`;
                    }
                    if (item.island) {
                        displayText +=
                            `, <small class="text-muted d-none d-sm-inline">${item.island}</small>`;
                    }
                    if (item.country) {
                        displayText += `, <small class="text-muted">${item.country}</small>`;
                    }
                    div.innerHTML = displayText;

                    const applySelection = () => {
                        cityInput.value = item.city;
                        if (countryInput) {
                            countryInput.value = item.country || '';
                        }
                        if (provinceInput) {
                            provinceInput.value = item.province || '';
                        }
                        if (islandInput) {
                            islandInput.value = item.island || '';
                        }
                        citySelected = true;
                        cityInput.setCustomValidity('');
                        resultsContainer.classList.add('d-none');
                        resultsContainer.innerHTML = '';
                    };

                    div.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                        applySelection();
                    });

                    div.addEventListener('click', applySelection);

                    resultsContainer.appendChild(div);
                });

                if (uniqueKeys.size > 0) {
                    resultsContainer.classList.remove('d-none');
                } else {
                    resultsContainer.classList.add('d-none');
                }
            };

            cityInput.addEventListener('input', function() {
                citySelected = false;
                if (countryInput) {
                    countryInput.value = '';
                }
                if (provinceInput) {
                    provinceInput.value = '';
                }
                if (islandInput) {
                    islandInput.value = '';
                }
                cityInput.setCustomValidity('');

                const query = this.value;
                if (query.length < 3) {
                    resultsContainer.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                    return;
                }

                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const localUrl = `/api/localidades/search?q=${encodeURIComponent(query)}`;
                    const iqUrl =
                        `https://api.locationiq.com/v1/autocomplete?key=${LOCATIONIQ_KEY}&q=${encodeURIComponent(query)}&limit=5&tag=place:city,place:town,place:village,place:hamlet`;

                    fetch(localUrl)
                        .then(response => response.ok ? response.json() : [])
                        .then(localData => {
                            const localResults = Array.isArray(localData) ? localData.map(item => ({
                                city: item.city,
                                province: item.province,
                                island: item.island,
                                country: item.country || 'España',
                            })) : [];

                            if (localResults.length > 0) {
                                renderSuggestions(localResults);
                                return;
                            }

                            return fetch(iqUrl)
                                .then(response => response.ok ? response.json() : [])
                                .then(iqData => {
                                    if (!Array.isArray(iqData)) {
                                        renderSuggestions(localResults);
                                        return;
                                    }
                                    const iqFormatted = iqData.map(item => {
                                        const address = item.address || {};
                                        return {
                                            city: address.city || address.town ||
                                                address.village || address.hamlet ||
                                                address.name,
                                            country: address.country || '',
                                            province: address.province || address
                                                .state || address.county || '',
                                            island: address.island || '',
                                        };
                                    });
                                    renderSuggestions([...localResults, ...iqFormatted]);
                                })
                                .catch(() => renderSuggestions(localResults));
                        })
                        .catch(() => {
                            resultsContainer.classList.add('d-none');
                        });
                }, 250);
            });

            const showCitySelectionError = () => {
                cityInput.setCustomValidity(citySelectionMessage);
                cityInput.reportValidity();
            };

            cityInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !citySelected) {
                    event.preventDefault();
                    showCitySelectionError();
                }
            });

            cityInput.addEventListener('blur', function() {
                if (cityInput.value.trim().length > 0 && !citySelected) {
                    showCitySelectionError();
                }
            });

            document.addEventListener('click', function(event) {
                if (!wrapper.contains(event.target)) {
                    resultsContainer.classList.add('d-none');
                }
            });
        };
    </script>
@endonce

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initLocationAutocomplete !== 'function') {
            return;
        }

        window.initLocationAutocomplete({!! json_encode([
            'citySelector' => $citySelector,
            'countrySelector' => $countrySelector,
            'provinceSelector' => $provinceSelector,
            'islandSelector' => $islandSelector,
        ]) !!});
    });
</script>
