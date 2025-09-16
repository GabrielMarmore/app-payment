function showToast(success, message) {
    const rootStyles = getComputedStyle(document.documentElement);

    const colors = {
        success: rootStyles.getPropertyValue('--bs-success').trim(),
        error: rootStyles.getPropertyValue('--bs-danger').trim(),
    };

    const bgColor = success ? colors.success : colors.error;

    const defaultMessage = success
        ? 'Ação realizada com sucesso!'
        : 'Erro ao realizar a ação.';

    Toastify({
        text: message || defaultMessage,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: { 
            background: bgColor
        },
        stopOnFocus: true
    }).showToast();
}