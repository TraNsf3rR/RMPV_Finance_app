function filterByPeriod(period) {
    const today = new Date();
    const fromDateInput = document.querySelector('input[name="from_date"]');
    const toDateInput = document.querySelector('input[name="to_date"]');
    const form = document.querySelector('form.filter-grid');
    
    let fromDate = null;
    
    switch(period) {
        case 'today':
            fromDate = new Date(today);
            break;
        case 'week':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 7);
            break;
        case 'month':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 30);
            break;
        case '3months':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 90);
            break;
        case 'year':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 365);
            break;
        case 'all':
            fromDateInput.value = '';
            toDateInput.value = '';
            form.submit();
            return;
    }
    
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    fromDateInput.value = formatDate(fromDate);
    toDateInput.value = formatDate(today);
    
    form.submit();
}
