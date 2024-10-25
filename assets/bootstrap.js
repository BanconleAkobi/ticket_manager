import { startStimulusApp } from '@symfony/stimulus-bridge';

// Crée et configure automatiquement l'application Stimulus
const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!',
    true,
    /\.(j|t)sx?$/
));