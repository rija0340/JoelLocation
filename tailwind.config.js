/** @type {import('tailwindcss').Config} */
module.exports = {
  content: {
    files: [
      './templates/**/*.twig',
      './templates/**/*.html.twig',
      './templates/vitrine/**/*.twig',
      './templates/client2/**/*.twig',
      './assets/vitrine/**/*.js',
      './assets/vitrine/**/*.vue',
      './assets/vitrine/**/*.jsx',
      './assets/vitrine/**/*.tsx',
      './assets/vitrine/**/*.css',
    ],
    extract: {
      twig: (content) => {
        // Extract class names from Twig templates
        const classRegex = /class\s*=\s*["']([^"']+)["']/g;
        const classes = [];
        let match;
        while ((match = classRegex.exec(content)) !== null) {
          classes.push(...match[1].split(/\s+/));
        }
        return classes;
      }
    }
  },
  safelist: [
    // Force include all commonly used utilities
    'px-4', 'px-6', 'px-8', 'py-3', 'py-16',
    'mb-2', 'mb-4', 'mb-6', 'mb-8', 'mb-12', 'mb-16',
    'mt-4', 'mt-6', 'mt-8',
    'gap-4', 'gap-8',
    'p-6',
    // Layout utilities
    'h-1', 'h-4', 'h-16', 'h-48', 'h-full',
    'w-4', 'w-16', 'w-24', 'w-full',
    'max-w-2xl', 'max-w-3xl',
    // Text and background colors
    'text-joel-red', 'text-joel-beige', 'text-joel-dark', 'text-joel-gray',
    'bg-joel-red', 'bg-joel-beige', 'bg-joel-red-dark', 'bg-joel-beige/20',
    'text-white', 'text-gray-200', 'text-gray-600', 'text-gray-700',
    'bg-white', 'bg-gray-100',
    // Hover variants
    'hover:bg-joel-red-dark', 'hover:bg-white', 'hover:bg-gray-100',
    'hover:text-joel-dark', 'hover:text-joel-red',
    'hover:underline', 'hover:scale-105', 'hover:shadow-lg',
    'hover:bg-opacity-75',
    // Additional utilities
    'rounded-full', 'rounded-lg', 'shadow-lg',
    'transition-all', 'duration-300',
    'font-semibold', 'font-bold',
    'text-sm', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl', 'text-6xl',
    'leading-tight',
    'border-2', 'border-white',
    'bg-opacity-50', 'bg-opacity-75',
    'object-cover',
    'inline-flex', 'items-center',
    'ml-2',
  ],
  theme: {
    extend: {
      colors: {
        'joel-red': '#af0000',
        'joel-red-dark': '#8b0000',
        'joel-red-light': '#d32f2f',
        'joel-beige': '#EFE7DB',
        'joel-dark': '#333333',
        'joel-gray': '#666666',
      },
      fontFamily: {
        'source': ['Source Sans Pro', 'sans-serif'],
        'sofia': ['Sofia Sans', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.6s ease-out',
        'bounce-gentle': 'bounceGentle 2s infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(30px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        bounceGentle: {
          '0%, 20%, 50%, 80%, 100%': { transform: 'translateY(0)' },
          '40%': { transform: 'translateY(-10px)' },
          '60%': { transform: 'translateY(-5px)' },
        }
      }
    }
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: ["light", "dark", "cupcake", "bumblebee", "emerald", "corporate", "synthwave", "retro", "cyberpunk", "valentine", "halloween", "garden", "forest", "aqua", "lofi", "pastel", "fantasy", "wireframe", "black", "luxury", "dracula", "cmyk", "autumn", "business", "acid", "lemonade", "night", "coffee", "winter"],
  },
}