(() => {
    'use strict';

    const resultadoSection = document.querySelector('.resultado');
    const markdownField = document.getElementById('resultado-markdown');
    const copyButton = document.getElementById('btn-copiar-nota');
    const downloadButton = document.getElementById('btn-descargar-nota');

    if (!resultadoSection || !markdownField || !copyButton || !downloadButton) {
        return;
    }

    const notaId = resultadoSection.dataset.notaId || '0';

    const getMarkdown = () => markdownField.textContent || '';

    const mostrarFeedbackCopia = () => {
        const originalText = copyButton.textContent;
        copyButton.textContent = '¡Copiado!';
        copyButton.disabled = true;

        window.setTimeout(() => {
            copyButton.textContent = originalText;
            copyButton.disabled = false;
        }, 2000);
    };

    async function copiarNota() {
        const markdown = getMarkdown();

        if (markdown.trim() === '') {
            return;
        }

        try {
            await navigator.clipboard.writeText(markdown);
            mostrarFeedbackCopia();
        } catch (error) {
            // Fallback para navegadores sin permiso de clipboard.
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = markdown;
            tempTextArea.style.position = 'fixed';
            tempTextArea.style.left = '-9999px';
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand('copy');
            document.body.removeChild(tempTextArea);
            mostrarFeedbackCopia();
        }
    }

    function descargarTxt() {
        const markdown = getMarkdown();

        if (markdown.trim() === '') {
            return;
        }

        const blob = new Blob([markdown], { type: 'text/plain;charset=utf-8' });
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = downloadUrl;
        link.download = `nota-${notaId}-redacta.txt`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(downloadUrl);
    }

    copyButton.addEventListener('click', copiarNota);
    downloadButton.addEventListener('click', descargarTxt);
})();
