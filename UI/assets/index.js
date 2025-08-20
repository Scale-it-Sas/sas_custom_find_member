jQuery(document).ready(function($) {
    const $selectElement = $('#consultingServices');
    const $customSelectDisplay = $('#customSelectDisplay');
    const $selectedOptionsContainer = $('#selectedOptionsContainer');
    const $searchInputField = $selectedOptionsContainer.find('.select2-search__field');
    const $customDropdownList = $('#customDropdownList');

    let selectedValues = new Set();
    const optionsData = [];
    
    $selectElement.find('option').each(function() {
        optionsData.push({
            value: $(this).val(),
            text: $(this).text()
        });
    });

    // Function to render selected options
    const renderSelectedOptions = () => {
        // Clear existing selected tags except the search input
        $selectedOptionsContainer.find('li:not(.select2-search--inline)').remove();

        selectedValues.forEach(value => {
            const optionText = optionsData.find(opt => opt.value === value)?.text;
            if (optionText) {
                const $li = $('<li>')
                    .addClass('select2-selection__choice')
                    .attr('title', optionText)
                    .html(`
                        <span class="select2-selection__choice__remove" data-value="${value}" role="presentation">× </span>
                        <span class="sas-consulting-option-val"> ${optionText} </span>
                    `);
                $li.insertBefore($searchInputField.parent());
            }
        });

        // Update the hidden select element's selected options
        $selectElement.find('option').each(function() {
            $(this).prop('selected', selectedValues.has($(this).val()));
        });

        // Adjust input field width
        if (selectedValues.size > 0) {
            $searchInputField.css('width', 'auto');
        } else {
            $searchInputField.css('width', '100%');
        }
    };

    // Function to render dropdown list
    const renderDropdownList = (filterText = '') => {
        $customDropdownList.empty();
        const filteredOptions = optionsData.filter(option =>
            option.text.toLowerCase().includes(filterText.toLowerCase())
        );

        if (filteredOptions.length === 0) {
            const $noResults = $('<div>')
                .text('No results found')
                .addClass('text-gray-500 italic')
                .css({
                    'padding': '0.5rem 0.75rem',
                    'font-style': 'italic',
                    'color': '#6b7280'
                });
            $customDropdownList.append($noResults);
            return;
        }

        filteredOptions.forEach(option => {
            const $div = $('<div>')
                .text(option.text)
                .attr('data-value', option.value)
                .addClass(selectedValues.has(option.value) ? 'selected' : '')
                .css({
                    'padding': '0.5rem 0.75rem',
                    'cursor': 'pointer',
                    'fontSize': '0.875rem',
                    'borderBottom': '1px solid #f3f4f6',
                    'backgroundColor': selectedValues.has(option.value) ? '#3b82f6' : '#fff',
                    'color': selectedValues.has(option.value) ? 'white' : '#374151'
                });

            $div.on('click', (e) => {
                e.stopPropagation();
                if (selectedValues.has(option.value)) {
                    selectedValues.delete(option.value);
                } else {
                    selectedValues.add(option.value);
                }
                renderSelectedOptions();
                renderDropdownList($searchInputField.val());
                $searchInputField.focus();
            });

            $div.on('mouseenter', function() {
                if (!selectedValues.has(option.value)) {
                    $(this).css('backgroundColor', '#f3f4f6');
                }
            });

            $div.on('mouseleave', function() {
                if (!selectedValues.has(option.value)) {
                    $(this).css('backgroundColor', '#fff');
                }
            });

            $customDropdownList.append($div);
        });
    };

    // Toggle dropdown visibility
    $customSelectDisplay.on('click', (e) => {
        // Check if the click target or any of its parents is the remove span
        if ($(e.target).hasClass('select2-selection__choice__remove')) {
            // Handle removal of selected option
            const valueToRemove = $(e.target).attr('data-value');
            selectedValues.delete(valueToRemove);
            renderSelectedOptions();
            renderDropdownList($searchInputField.val());
            $searchInputField.focus();
            e.stopPropagation(); 
        } else {
            $customDropdownList.toggleClass('hidden');
            if (!$customDropdownList.hasClass('hidden')) {
                renderDropdownList($searchInputField.val());
            }
            $searchInputField.focus();
        }
    });

    // Close dropdown when clicking outside
    $(document).on('click', (e) => {
        // Check if the click is outside both the custom select display and the dropdown list
        if (!$customSelectDisplay.is(e.target) && !$customSelectDisplay.has(e.target).length &&
            !$customDropdownList.is(e.target) && !$customDropdownList.has(e.target).length) {
            $customDropdownList.addClass('hidden');
        }
    });

    // Filter options based on search input
    $searchInputField.on('input', (e) => {
        renderDropdownList($searchInputField.val());
        $customDropdownList.removeClass('hidden');
    });

    // Initial render
    renderSelectedOptions();
    renderDropdownList();

    // function fetchMembers(page = 1) {
    //     const keyword = {};
    //     const SearchResult = $('#sas-search-result');

    //     keyword.company = String($('#companyName').val() || '').trim();
    //     keyword.first_name = String($('#firstName').val() || '').trim();
    //     keyword.surname = String($('#surname').val() || '').trim();

    //     const services = $('#consultingServices').val();
    //     if (Array.isArray(services) && services.length > 0) {
    //         keyword.services = services;
    //     }

    //     $.ajax({
    //         url: sas_ajax_obj.ajax_url,
    //         method: 'POST',
    //         data: {
    //             action: 'sas_find_member',
    //             keyword: keyword,
    //             paged: page,
    //             _ajax_nonce: sas_ajax_obj.nonce
    //         },
    //         success: function (response) {
    //             console.log(response.data)
    //             if (response.success) {
    //                 SearchResult.removeClass('hidden')
    //                 $('#sas-search-result').html(response.data);
    //             } else {
    //                 $('#sas-search-result').html('<p>No results found.</p>');
    //                 SearchResult.removeClass('hidden')
    //             }
    //         },
    //         error: function (response) {
    //             console.log(response)
    //             $('#sas-search-result').html('<p>Error loading results.</p>');
    //             SearchResult.removeClass('hidden');
    //             $('#sas-search-result').html(response.responseText);
    //         }
    //     });
    // }

    // $('#sas-search-form').on('submit', function (e) {
    //     e.preventDefault();
    //     fetchMembers(1); // Reset to page 1 on form submit
    // });
    //  // Handle pagination click (uses event delegation)
    //  $(document).on('click', '.sas-pagination a', function (e) {
    //     e.preventDefault();
    //     const page = $(this).data('page');
    //     fetchMembers(page);
    // });

    let isSearching = false; // Prevent multiple simultaneous requests

    function fetchMembers(page = 1) {
        if (isSearching) {
            return;
        }

        const keyword = {};
        const SearchResult = $('#sas-search-result');
        const submitButton = $('#sas-search-form button[type="submit"]');

        keyword.company = String($('#companyName').val() || '').trim();
        keyword.first_name = String($('#firstName').val() || '').trim();
        keyword.surname = String($('#surname').val() || '').trim();

        // Collect dynamic ACF field values
        $('#sas-search-form input[type="text"]').each(function() {
            const fieldName = $(this).attr('name');
            const fieldValue = String($(this).val() || '').trim();
            
            // Skip if it's one of the default fields we already handled
            if (fieldName && fieldName !== 'companyName' && fieldName !== 'firstName' && fieldName !== 'surname' && fieldValue) {
                keyword[fieldName] = fieldValue;
            }
        });

        console.log('All search keywords:', keyword);

        const services = $('#consultingServices').val();
        if (Array.isArray(services) && services.length > 0) {
            keyword.services = services;
        }

        // Allow search with no criteria to show all members
        // const hasSearchCriteria = keyword.company || keyword.first_name || keyword.surname || keyword.services;
        
        // if (!hasSearchCriteria) {
        //     SearchResult.html('<p class="error-message">Please enter at least one search criterion.</p>').removeClass('hidden');
        //     return;
        // }

        isSearching = true;
        SearchResult.html('<div class="loading-spinner"><svg width="60" height="60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="#DCB389" stroke="#DCB389" stroke-width="15" r="15" cx="40" cy="100"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4"></animate></circle><circle fill="#DCB389" stroke="#DCB389" stroke-width="15" r="15" cx="100" cy="100"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2"></animate></circle><circle fill="#DCB389" stroke="#DCB389" stroke-width="15" r="15" cx="160" cy="100"><animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="0"></animate></circle></svg></div>').removeClass('hidden');
        submitButton.prop('disabled', true).text('Searching...');

        $.ajax({
            url: sas_ajax_obj.ajax_url,
            method: 'POST',
            dataType: 'text',
            data: {
                action: 'sas_find_member',
                keyword: keyword,
                paged: page,
                _ajax_nonce: sas_ajax_obj.nonce
            },
            timeout: 30000,
            success: function (rawResponse) {
                // console.log('Raw response:', rawResponse);
                
                try {
                    let jsonResponse;
                    
                    const jsonMatch = rawResponse.match(/(\{.*\}|\[.*\])$/);
                    
                    if (jsonMatch) {
                        jsonResponse = JSON.parse(jsonMatch[1]);
                    } else {
                        throw new Error('No JSON found in response');
                    }
                    
                    // console.log('Parsed JSON:', jsonResponse);
                    
                    if (jsonResponse.success && jsonResponse.data) {
                        SearchResult.html(jsonResponse.data);
                    } else {
                        const errorMsg = jsonResponse.data || 'No results found.';
                        SearchResult.html(`<p class="no-results">${errorMsg}</p>`);
                    }
                    
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    // console.error('Raw response that failed to parse:', rawResponse);
                    
                    if (rawResponse.includes('No members found')) {
                        SearchResult.html('<p class="no-results">No members found matching your criteria.</p>');
                    } else {
                        SearchResult.html('<p class="error-message">Error parsing server response. Please try again.</p>');
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                
                let errorMessage = '<p class="error-message">Error loading results. Please try again.</p>';
                
                if (status === 'timeout') {
                    errorMessage = '<p class="error-message">Search timed out. Please try again.</p>';
                } else if (status === 'abort') {
                    errorMessage = '<p class="error-message">Search was cancelled.</p>';
                } else if (xhr.status === 403) {
                    errorMessage = '<p class="error-message">Access denied. Please refresh the page and try again.</p>';
                } else if (xhr.status >= 500) {
                    errorMessage = '<p class="error-message">Server error. Please try again later.</p>';
                }
                
                SearchResult.html(errorMessage);
                
                if (xhr.responseText) {
                    console.error('Server response:', xhr.responseText);
                    
                    if (xhr.responseText.includes('No members found')) {
                        SearchResult.html('<p class="no-results">No members found matching your criteria.</p>');
                    }
                }
            },
            complete: function() {
                isSearching = false;
                submitButton.prop('disabled', false).text('Search');
            }
        });
    }

    // Form submission handler
    $('#sas-search-form').on('submit', function (e) {
        e.preventDefault();
        fetchMembers(1);
    });

    $(document).on('click', '.sas-pagination a', function (e) {
        e.preventDefault();
        
        if (isSearching) {
            return;
        }
        
        const page = parseInt($(this).data('page'));
        
        if (isNaN(page) || page < 1) {
            console.error('Invalid page number:', $(this).data('page'));
            return;
        }
        
        $('.sas-pagination a').removeClass('active');
        $(this).addClass('active');
        
        fetchMembers(page);
    });

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    const debouncedSearch = debounce(() => {
        // Only auto-search if there's some content
        const hasContent = $('#companyName').val().trim() || 
            $('#firstName').val().trim() || 
            $('#surname').val().trim() ||
            ($('#consultingServices').val() && $('#consultingServices').val().length > 0);
        
        if (hasContent) {
            fetchMembers(1);
        }
    }, 500);

    // Uncomment the lines below if you want auto-search functionality
    // $('#companyName, #firstName, #surname').on('input', debouncedSearch);
    // $('#consultingServices').on('change', debouncedSearch);

    // Clear results when form is reset
    $('#sas-search-form').on('reset', function() {
        $('#sas-search-result').addClass('hidden').empty();
    });
});

