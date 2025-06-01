import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['progress', 'progressBar', 'status'];
    static values = {
        jobId: String,
        pollInterval: { type: Number, default: 2000 }
    };

    connect() {
        if (this.hasJobIdValue) {
            this.startPolling();
        }
    }

    disconnect() {
        this.stopPolling();
    }

    startPolling() {
        this.showProgress();
        this.pollInterval = setInterval(() => {
            this.checkProgress();
        }, this.pollIntervalValue);
    }

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    async checkProgress() {
        try {
            const response = await fetch(`/api/ai/jobs/${this.jobIdValue}/status`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            this.updateProgress(data);

            if (data.status === 'completed' || data.status === 'failed') {
                this.stopPolling();
                if (data.status === 'completed') {
                    window.location.reload();
                } else {
                    this.showError('Generowanie fiszek nie powiodło się. Spróbuj ponownie.');
                }
            }
        } catch (error) {
            this.stopPolling();
            this.showError('Wystąpił błąd podczas sprawdzania postępu. Spróbuj odświeżyć stronę.');
        }
    }

    updateProgress(data) {
        const progress = data.progress || 0;
        this.progressBarTarget.style.width = `${progress}%`;
        
        let statusText = 'Generowanie fiszek';
        switch (data.status) {
            case 'processing':
                statusText = `Generowanie fiszek (${progress}%)`;
                break;
            case 'completed':
                statusText = 'Generowanie zakończone!';
                break;
            case 'failed':
                statusText = 'Generowanie nie powiodło się';
                break;
        }
        this.statusTarget.textContent = statusText;
    }

    showProgress() {
        this.progressTarget.classList.remove('hidden');
    }

    hideProgress() {
        this.progressTarget.classList.add('hidden');
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'mt-4 p-4 bg-red-100 text-red-700 rounded-lg';
        errorDiv.textContent = message;
        this.element.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
} 