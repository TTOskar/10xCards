import { Controller } from '@hotwired/stimulus';
import { Modal } from 'flowbite';

export default class extends Controller {
    static targets = ['container', 'item', 'select', 'error'];
    static values = {
        jobId: String
    };

    connect() {
        // Initialize the edit modal
        this.editModal = new Modal(document.getElementById('edit-flashcard-modal'));
        
        // Initialize form handlers
        this.initializeEditForm();
        
        // Initialize bulk action buttons
        this.initializeBulkActions();
    }

    initializeEditForm() {
        const form = document.getElementById('edit-flashcard-form');
        if (!form) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const flashcardId = document.getElementById('edit-flashcard-id').value;
            const front = document.getElementById('edit-flashcard-front').value;
            const back = document.getElementById('edit-flashcard-back').value;

            try {
                await this.updateFlashcardStatus(flashcardId, 'edited', {
                    edited_front: front,
                    edited_back: back
                });

                this.editModal.hide();
                this.showSuccess('Flashcard updated successfully');
            } catch (error) {
                this.showError('Failed to update flashcard');
            }
        });
    }

    initializeBulkActions() {
        document.getElementById('accept-all').addEventListener('click', () => this.bulkAction('accept'));
        document.getElementById('reject-all').addEventListener('click', () => this.bulkAction('reject'));
        document.getElementById('save-selected').addEventListener('click', () => this.saveSelected());
    }

    async bulkAction(action) {
        try {
            await fetch(`/api/ai/jobs/${this.jobIdValue}/bulk-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action === 'accept' ? 'save' : 'reject',
                    deck_id: document.getElementById('deck-select')?.value
                })
            });

            if (action === 'accept') {
                window.location.href = '/deck';
            } else {
                this.itemTargets.forEach(item => {
                    item.classList.add('opacity-50');
                });
                this.showSuccess('All flashcards rejected');
            }
        } catch (error) {
            this.showError('Failed to process flashcards');
        }
    }

    async accept(event) {
        const flashcardId = event.currentTarget.dataset.flashcardId;
        await this.updateFlashcardStatus(flashcardId, 'accepted');
    }

    async reject(event) {
        const flashcardId = event.currentTarget.dataset.flashcardId;
        await this.updateFlashcardStatus(flashcardId, 'rejected');
    }

    edit(event) {
        const flashcardId = event.currentTarget.dataset.flashcardId;
        const flashcardElement = document.querySelector(`[data-flashcard-id="${flashcardId}"]`);
        
        // Populate the edit form
        document.getElementById('edit-flashcard-id').value = flashcardId;
        document.getElementById('edit-flashcard-front').value = 
            flashcardElement.querySelector('.front-content').textContent.trim();
        document.getElementById('edit-flashcard-back').value = 
            flashcardElement.querySelector('.back-content').textContent.trim();

        // Show the modal
        this.editModal.show();
    }

    async updateFlashcardStatus(flashcardId, status, additionalData = {}) {
        try {
            const response = await fetch(`/api/ai/flashcards/${flashcardId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status,
                    ...additionalData
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update flashcard status');
            }

            const flashcardElement = document.querySelector(`[data-flashcard-id="${flashcardId}"]`);
            if (flashcardElement) {
                // Update UI to reflect the new status
                flashcardElement.dataset.status = status;
                if (status === 'rejected') {
                    flashcardElement.classList.add('opacity-50');
                } else if (status === 'accepted') {
                    flashcardElement.classList.add('border-green-500');
                }
            }

            this.showSuccess('Flashcard updated successfully');
        } catch (error) {
            this.showError('Failed to update flashcard');
            throw error;
        }
    }

    showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    showError(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
} 