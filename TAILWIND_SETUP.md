# Tailwind CSS Setup Guide for AutoClean BD

## Overview
This guide will help you convert the AutoClean BD website to use Tailwind CSS for a modern, responsive design.

## Files Created/Modified

### 1. Configuration Files
- `package.json` - Node.js dependencies and build scripts
- `tailwind.config.js` - Tailwind CSS configuration with custom theme
- `assets/css/input.css` - Tailwind directives and custom styles

### 2. Key Features Implemented

#### Custom Color Palette
```javascript
colors: {
  primary: {
    50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
    400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
    800: '#1e40af', 900: '#1e3a8a'
  },
  dark: {
    50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
    400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
    800: '#1e293b', 900: '#0f172a'
  }
}
```

#### Custom Animations
- `fade-in` - Smooth fade in animation
- `slide-in` - Slide up animation
- `bounce-in` - Bounce scale animation
- `toast-slide` - Toast notification slide animation

#### Custom Components
- `.btn-primary` - Primary button with gradient
- `.btn-secondary` - Secondary button
- `.card` - Card component with hover effects
- `.modal` - Modal overlay and content
- `.toast` - Toast notification system
- `.form-input` - Form input styling
- `.service-card` - Service card with hover effects

## Installation Steps

### 1. Install Dependencies
```bash
npm install
```

### 2. Build Tailwind CSS
```bash
# Development (watch mode)
npm run build

# Production (minified)
npm run build-prod
```

### 3. HTML Structure Changes

#### Navigation
```html
<nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <a href="#" class="text-2xl font-bold text-gradient">
          <i class="fas fa-car-wash mr-2"></i>AutoClean BD
        </a>
      </div>
      
      <!-- Navigation Links -->
      <div class="hidden md:flex items-center space-x-8">
        <a href="#home" class="nav-link">Home</a>
        <a href="#services" class="nav-link">Services</a>
        <a href="#about" class="nav-link">About</a>
        <a href="#contact" class="nav-link">Contact</a>
      </div>
      
      <!-- Buttons -->
      <div class="flex items-center space-x-4">
        <button class="btn-secondary">Login</button>
        <button class="btn-primary">Register</button>
      </div>
    </div>
  </div>
</nav>
```

#### Hero Section
```html
<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-dark-900 via-dark-800 to-primary-900/20 pt-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      <!-- Content -->
      <div class="space-y-8 animate-fade-in">
        <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
          Professional Vehicle Wash Services
          <span class="text-gradient block">in Bangladesh</span>
        </h1>
        <p class="text-xl text-dark-300 leading-relaxed">
          Experience premium car and bike washing services...
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
          <button class="btn-primary">Book Now</button>
          <button class="btn-secondary">Contact Us</button>
        </div>
      </div>
      
      <!-- Image -->
      <div class="relative animate-slide-in">
        <img src="..." class="w-full h-96 object-cover rounded-2xl shadow-2xl">
      </div>
    </div>
  </div>
</section>
```

#### Service Cards
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
  <div class="service-card bg-dark-700 border border-dark-600 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:border-primary-500 hover:transform hover:-translate-y-2">
    <div class="service-image mb-4">
      <img src="..." class="w-full h-48 object-cover rounded-lg transition-transform duration-300 hover:scale-105">
    </div>
    <div class="service-icon mb-4">
      <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center">
        <i class="fas fa-car text-white text-xl"></i>
      </div>
    </div>
    <div class="service-content">
      <h3 class="text-xl font-bold mb-2 text-dark-100">Basic Wash</h3>
      <p class="text-dark-300 mb-4">Exterior wash, tire cleaning...</p>
      <div class="service-pricing mb-4 space-y-2">
        <div class="flex justify-between items-center">
          <span class="vehicle text-dark-300">Car</span>
          <span class="price text-lg font-bold text-primary-400">৳299</span>
        </div>
      </div>
      <button class="w-full btn-primary">Book Now</button>
    </div>
  </div>
</div>
```

#### Modals
```html
<div id="loginModal" class="modal fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300">
  <div class="modal-content bg-dark-800 border border-dark-700 rounded-2xl p-8 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl transform scale-95 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 text-dark-100">Login</h2>
    <form id="loginForm" class="space-y-6">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" class="form-input" required>
      </div>
      <button type="submit" class="w-full btn-primary">Login</button>
    </form>
  </div>
</div>
```

#### Toast Notifications
```html
<div id="toast" class="toast fixed top-5 right-5 bg-dark-800 border border-dark-600 rounded-2xl p-6 text-dark-100 font-medium shadow-2xl transform translate-x-full scale-95 transition-all duration-400 z-[10000] max-w-sm flex items-center gap-3 opacity-0">
  <div class="toast-icon">
    <i class="fas fa-info-circle"></i>
  </div>
  <div class="toast-content">
    <div class="toast-title">Notification</div>
    <div class="toast-message">This is a notification message.</div>
  </div>
  <button class="toast-close">
    <i class="fas fa-times"></i>
  </button>
</div>
```

## Key Benefits

### 1. Responsive Design
- Mobile-first approach
- Breakpoint system (sm, md, lg, xl)
- Flexible grid system

### 2. Dark Theme
- Custom dark color palette
- Glassmorphism effects
- Gradient backgrounds

### 3. Modern Animations
- Smooth transitions
- Hover effects
- Loading states

### 4. Accessibility
- Focus states
- Screen reader support
- Keyboard navigation

### 5. Performance
- Purged CSS (only used classes)
- Optimized bundle size
- Fast loading

## Usage Examples

### Buttons
```html
<button class="btn-primary">Primary Button</button>
<button class="btn-secondary">Secondary Button</button>
<button class="btn-outline">Outline Button</button>
```

### Cards
```html
<div class="card">
  <h3>Card Title</h3>
  <p>Card content...</p>
</div>
```

### Forms
```html
<div class="form-group">
  <label class="form-label">Label</label>
  <input type="text" class="form-input" placeholder="Enter text...">
</div>
```

### Toast Notifications
```javascript
showToast('Success!', 'Operation completed successfully.', 'success');
showToast('Error!', 'Something went wrong.', 'error');
showToast('Warning!', 'Please check your input.', 'warning');
showToast('Info!', 'Here is some information.', 'info');
```

## Next Steps

1. **Complete HTML Conversion**: Convert all remaining HTML sections
2. **JavaScript Integration**: Update JavaScript to work with new classes
3. **Testing**: Test responsiveness and functionality
4. **Optimization**: Optimize images and performance
5. **Deployment**: Deploy with optimized build

## Troubleshooting

### Common Issues

1. **Styles not applying**: Check if Tailwind CSS is properly loaded
2. **Responsive issues**: Verify breakpoint classes are correct
3. **Animation not working**: Ensure custom animations are defined
4. **Build errors**: Check Node.js and npm installation

### Development Tips

1. Use browser dev tools to inspect classes
2. Test on multiple screen sizes
3. Validate HTML structure
4. Check accessibility features

## Resources

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Tailwind CSS Components](https://tailwindui.com/)
- [Custom CSS with Tailwind](https://tailwindcss.com/docs/adding-custom-styles)
- [Responsive Design](https://tailwindcss.com/docs/responsive-design) 