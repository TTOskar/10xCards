import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'charCount', 'submitButton', 'errorMessage'];
    static values = {
        minLength: Number,
        maxLength: Number
    };

    connect() {
        this.validateInput();
        this.updateCharCount();
    }

    validateInput() {
        const input = this.inputTarget;
        const length = input.value.length;
        const isValid = length >= this.minLengthValue && length <= this.maxLengthValue;

        this.submitButtonTarget.disabled = !isValid;
        
        if (!isValid) {
            if (length < this.minLengthValue) {
                this.errorMessageTarget.textContent = `Tekst jest za krótki. Minimalna długość to ${this.minLengthValue} znaków.`;
            } else if (length > this.maxLengthValue) {
                this.errorMessageTarget.textContent = `Tekst jest za długi. Maksymalna długość to ${this.maxLengthValue} znaków.`;
            }
            this.errorMessageTarget.classList.remove('hidden');
        } else {
            this.errorMessageTarget.classList.add('hidden');
        }
    }

    updateCharCount() {
        const length = this.inputTarget.value.length;
        this.charCountTarget.textContent = `${length}/${this.maxLengthValue}`;
        
        // Update color based on remaining characters
        const remaining = this.maxLengthValue - length;
        if (remaining < 100) {
            this.charCountTarget.classList.add('text-red-500');
            this.charCountTarget.classList.remove('text-gray-500');
        } else {
            this.charCountTarget.classList.add('text-gray-500');
            this.charCountTarget.classList.remove('text-red-500');
        }
    }

    onInput() {
        this.validateInput();
        this.updateCharCount();
    }
} 