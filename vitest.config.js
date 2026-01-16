import { defineConfig } from 'vitest/config';

export default defineConfig( {
	test: {
		globals: true,
		environment: 'jsdom',
		setupFiles: [ './src/js/__tests__/setup.js' ],
		include: [ 'src/js/**/*.test.{js,jsx}' ],
		coverage: {
			provider: 'v8',
			reporter: [ 'text', 'html' ],
			include: [ 'src/js/**/*.{js,jsx}' ],
			exclude: [ 'src/js/__tests__/**' ],
		},
	},
} );
