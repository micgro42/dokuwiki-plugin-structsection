import {defineConfig} from 'eslint/config';
import js from '@eslint/js';

export default defineConfig([
    {
        files: ['**/*.js'],
        extends: ['js/recommended'],
        plugins: {js},
        languageOptions: {
            globals: {
                JSINFO: false,
                LANG: false,
                jQuery: false,
                createPicker: false,
                DOKU_BASE: false,
                pickercounter: true,
                pickerToggle: false,
                pickerInsert: false,
            },
        },
        rules: {
            indent: ['error', 4],
            'no-magic-numbers': ['warn', {'ignore': [0, 1, -1]}],
        }
    }
]);
