import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['flashcard', 'acceptAll', 'rejectAll', 'saveSelected', 'checkbox'];
    static values = {
        jobId: String
    };

    connect() {
        this.updateButtonStates();
    }

    async acceptAll() {
        this.flashcardTargets.forEach(flashcard => {
            if (!flashcard.classList.contains('accepted')) {
                this.accept(flashcard);
            }
        });
    }

    async rejectAll() {
        this.flashcardTargets.forEach(flashcard => {
            if (!flashcard.classList.contains('rejected')) {
                this.reject(flashcard);
            }
        });
    }

    async saveSelected() {
        const selectedFlashcards = this.flashcardTargets
            .filter(flashcard => flashcard.classList.contains('accepted'))
            .map(flashcard => flashcard.dataset.flashcardId);

        if (selectedFlashcards.length === 0) {
            this.showMessage('Wybierz przynajmniej jedną fiszkę do zapisania.', 'error');
            return;
        }

        try {
            const response = await fetch(`/api/ai/jobs/${this.jobIdValue}/bulk-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify({ flashcardIds: selectedFlashcards })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            this.showMessage('Fiszki zostały zapisane pomyślnie.', 'success');
            window.location.href = '/ai/flashcards';
        } catch (error) {
            this.showMessage('Wystąpił błąd podczas zapisywania fiszek. Spróbuj ponownie.', 'error');
        }
    }

    async accept(flashcard) {
        const id = flashcard.dataset.flashcardId;
        try {
            const response = await fetch(`/api/ai/flashcards/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify({ status: 'accepted' })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            flashcard.classList.add('accepted');
            flashcard.classList.remove('rejected');
            this.updateButtonStates();
        } catch (error) {
            this.showMessage('Nie udało się zaakceptować fiszki. Spróbuj ponownie.', 'error');
        }
    }

    async reject(flashcard) {
        const id = flashcard.dataset.flashcardId;
        try {
            const response = await fetch(`/api/ai/flashcards/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify({ status: 'rejected' })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            flashcard.classList.add('rejected');
            flashcard.classList.remove('accepted');
            this.updateButtonStates();
        } catch (error) {
            this.showMessage('Nie udało się odrzucić fiszki. Spróbuj ponownie.', 'error');
        }
    }

    updateButtonStates() {
        const hasAccepted = this.flashcardTargets.some(f => f.classList.contains('accepted'));
        const hasUnprocessed = this.flashcardTargets.some(f => 
            !f.classList.contains('accepted') && !f.classList.contains('rejected')
        );

        this.saveSelectedTarget.disabled = !hasAccepted;
        this.acceptAllTarget.disabled = !hasUnprocessed;
        this.rejectAllTarget.disabled = !hasUnprocessed;
    }

    showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `mt-4 p-4 ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} rounded-lg`;
        alertDiv.textContent = message;

        const container = this.element.querySelector('.flash-messages');
        container.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
} 