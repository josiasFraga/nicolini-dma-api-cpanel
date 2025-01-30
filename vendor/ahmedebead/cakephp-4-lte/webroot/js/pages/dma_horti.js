$(document).ready(function () {
    const filterForm = $('#filterForm');
    const applyFiltersBtn = $('#applyFilters');
    const exportExcelBtn = $('#exportExcel');
    const clearFiltersBtn = $('#clearFilters');

    // Apply Filters
    applyFiltersBtn.on('click', function () {
        const filters = filterForm.serializeArray().reduce(function (obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});
        window.location.href = `${window.location.pathname}?${$.param(filters)}`;
    });

    // Export to Excel
    exportExcelBtn.on('click', function () {
        const filters = filterForm.serialize();
        window.location.href = `/dma/exportHorti?${filters}`;
    });

    // Clear Filters
    clearFiltersBtn.on('click', function () {
        filterForm[0].reset(); // Reseta o formulário de filtros
    });

    $('#applySort').on('click', function () {
        $('#sortForm').submit();
    });
});
