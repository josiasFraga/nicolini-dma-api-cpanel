$(document).ready(function () {
    const filterForm = $('#filterForm');
    const applyFiltersBtn = $('#applyFilters');
    const exportExcelBtn = $('#exportExcel');
    const clearFiltersBtn = $('#clearFilters');

    // Load filters from localStorage
    const savedFilters = JSON.parse(localStorage.getItem('dmaFilters')) || {};
    $.each(savedFilters, function (key, value) {
        const input = filterForm.find(`[name="${key}"]`);
        if (input.length) {
            input.val(value);
        }
    });

    // Apply Filters
    applyFiltersBtn.on('click', function () {
        const filters = filterForm.serializeArray().reduce(function (obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});
        localStorage.setItem('dmaFilters', JSON.stringify(filters));
        window.location.href = `${window.location.pathname}?${$.param(filters)}`;
    });

    // Export to Excel
    exportExcelBtn.on('click', function () {
        const filters = filterForm.serialize();
        window.location.href = `/dma/export?${filters}`;
    });

    // Clear Filters
    clearFiltersBtn.on('click', function () {
        localStorage.removeItem('dmaFilters'); // Remove os filtros do localStorage
        filterForm[0].reset(); // Reseta o formulário de filtros
    });

    $('#applySort').on('click', function () {
        $('#sortForm').submit();
    });
});
