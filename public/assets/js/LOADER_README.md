# Universal Loader Component

## Installation
Include the loader script in your HTML:
```html
<script src="/assets/js/loader.js"></script>
```

## Usage Examples

### Basic Spinner Loader
```javascript
// Create a default spinner loader
const loader = Loader.create();
loader.show(); // Display loader
loader.hide(); // Hide loader
```

### Customized Loader
```javascript
// Customize loader appearance
const customLoader = Loader.create({
    type: 'dots',     // Options: 'spinner', 'dots', 'bars'
    color: '#e74c3c', // Custom color
    backgroundColor: 'rgba(0,0,0,0.5)', // Semi-transparent background
    zIndex: 10000     // High z-index
});

customLoader.show();
// Perform async operation
someAsyncFunction()
    .then(() => customLoader.hide())
    .catch(() => customLoader.hide());
```

## Loader Types
1. `spinner`: Rotating circular loader
2. `dots`: Bouncing dot animation
3. `bars`: Stretching vertical bars

## Options
- `type`: Loader animation style
- `color`: Primary loader color
- `backgroundColor`: Overlay background color
- `zIndex`: Stacking order of loader

## Best Practices
- Always hide loader after async operations
- Use appropriate loader type for context
- Customize color to match your design
