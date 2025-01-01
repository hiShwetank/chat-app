// Reusable Loader Utility
class Loader {
    constructor(options = {}) {
        this.options = {
            type: options.type || 'spinner', // spinner, dots, bars
            color: options.color || '#3498db',
            backgroundColor: options.backgroundColor || 'rgba(255, 255, 255, 0.8)',
            zIndex: options.zIndex || 9999
        };
        
        this.loaderElement = null;
        this.init();
    }

    init() {
        // Create loader container
        this.loaderElement = document.createElement('div');
        this.loaderElement.style.position = 'fixed';
        this.loaderElement.style.top = '0';
        this.loaderElement.style.left = '0';
        this.loaderElement.style.width = '100%';
        this.loaderElement.style.height = '100%';
        this.loaderElement.style.display = 'none';
        this.loaderElement.style.justifyContent = 'center';
        this.loaderElement.style.alignItems = 'center';
        this.loaderElement.style.backgroundColor = this.options.backgroundColor;
        this.loaderElement.style.zIndex = this.options.zIndex;

        // Create loader based on type
        const loaderContent = this.createLoaderContent();
        this.loaderElement.appendChild(loaderContent);

        // Append to body
        document.body.appendChild(this.loaderElement);
    }

    createLoaderContent() {
        const loaderContent = document.createElement('div');
        
        switch(this.options.type) {
            case 'spinner':
                loaderContent.innerHTML = this.createSpinnerLoader();
                break;
            case 'dots':
                loaderContent.innerHTML = this.createDotsLoader();
                break;
            case 'bars':
                loaderContent.innerHTML = this.createBarsLoader();
                break;
            default:
                loaderContent.innerHTML = this.createSpinnerLoader();
        }

        return loaderContent;
    }

    createSpinnerLoader() {
        return `
            <style>
                .spinner {
                    width: 50px;
                    height: 50px;
                    border: 5px solid ${this.options.color};
                    border-top: 5px solid transparent;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
            <div class="spinner"></div>
        `;
    }

    createDotsLoader() {
        return `
            <style>
                .dots-loader {
                    display: flex;
                    justify-content: center;
                }
                .dot {
                    width: 10px;
                    height: 10px;
                    margin: 0 5px;
                    background-color: ${this.options.color};
                    border-radius: 50%;
                    animation: bounce 0.5s ease-in infinite alternate;
                }
                .dot:nth-child(2) {
                    animation-delay: 0.1s;
                }
                .dot:nth-child(3) {
                    animation-delay: 0.2s;
                }
                @keyframes bounce {
                    to { transform: translateY(-10px); }
                }
            </style>
            <div class="dots-loader">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        `;
    }

    createBarsLoader() {
        return `
            <style>
                .bars-loader {
                    display: flex;
                    justify-content: center;
                }
                .bar {
                    width: 10px;
                    height: 30px;
                    margin: 0 3px;
                    background-color: ${this.options.color};
                    animation: stretch 1.2s ease-in-out infinite;
                }
                .bar:nth-child(2) {
                    animation-delay: 0.1s;
                }
                .bar:nth-child(3) {
                    animation-delay: 0.2s;
                }
                .bar:nth-child(4) {
                    animation-delay: 0.3s;
                }
                @keyframes stretch {
                    0%, 40%, 100% { transform: scaleY(0.4); }
                    20% { transform: scaleY(1); }
                }
            </style>
            <div class="bars-loader">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        `;
    }

    show() {
        if (this.loaderElement) {
            this.loaderElement.style.display = 'flex';
        }
    }

    hide() {
        if (this.loaderElement) {
            this.loaderElement.style.display = 'none';
        }
    }

    // Static method to create and manage loaders
    static create(options = {}) {
        return new Loader(options);
    }
}

// Export for module support
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Loader;
}

// Global exposure for non-module environments
window.Loader = Loader;
