/**
 * Receipt Sharing and Printing Logic
 */

function printReceipt(url) {
    const printWindow = window.open(url, '_blank');
    if (printWindow) {
        printWindow.onload = function () {
            printWindow.print();
        };
    }
}

function shareViaWhatsApp() {
    const { name, id, amount, downloadUrl } = window.receiptData;

    // Message content
    const message = `🎓 *Student Receipt - Al Akhirah International Academy*\n\n` +
        `👤 Student: ${name}\n` +
        `🆔 ID: ${id}\n` +
        `💰 Amount Paid: ৳${amount}\n\n` +
        `📎 Please download the receipt PDF and attach it to this message.`;

    // Download PDF
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `receipt_${id}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Open WhatsApp
    setTimeout(() => {
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
        alert('📥 PDF downloaded! Please attach it to your WhatsApp message.');
    }, 1000);
}

function shareViaEmail() {
    const { name, id, amount, downloadUrl } = window.receiptData;

    const subject = `Payment Receipt - ${name} (${id})`;
    const body = `Dear Parent/Guardian,\n\n` +
        `This is to confirm the payment receipt for ${name} (ID: ${id}).\n\n` +
        `Amount Paid: ৳${amount}\n\n` +
        `Please find the attached receipt PDF.\n\n` +
        `Thank you,\nAl Akhirah International Academy`;

    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `receipt_${id}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => {
        const mailtoUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        window.location.href = mailtoUrl;
        alert('📥 PDF downloaded! Please attach it to your email.');
    }, 1000);
}

async function shareReceipt() {
    const { name, id, amount, downloadUrl } = window.receiptData;

    if (!navigator.share) {
        alert('Web Share API is not supported in your browser. Please use WhatsApp or Email buttons.');
        return;
    }

    try {
        const loadingMsg = document.createElement('div');
        loadingMsg.innerHTML = '⏳ Preparing PDF for sharing...';
        loadingMsg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#333;color:#fff;padding:20px;border-radius:8px;z-index:9999;';
        document.body.appendChild(loadingMsg);

        const response = await fetch(downloadUrl);
        const blob = await response.blob();
        const file = new File([blob], `receipt_${id}.pdf`, { type: 'application/pdf' });

        document.body.removeChild(loadingMsg);

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: `Payment Receipt - ${name}`,
                text: `Payment receipt for ${name} (ID: ${id}). Amount: ৳${amount}`,
                files: [file]
            });
        } else {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `receipt_${id}.pdf`;
            link.click();
            alert('📥 PDF downloaded! Your browser doesn\'t support file sharing, but the file has been downloaded.');
        }
    } catch (error) {
        console.error('Error sharing PDF:', error);
        alert('❌ Error sharing PDF: ' + error.message);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (navigator.share) {
        const webShareBtn = document.getElementById('webShareBtn');
        if (webShareBtn) {
            webShareBtn.style.display = 'inline-flex';
        }
    }
});
