import { startStimulusApp } from '@symfony/stimulus-bridge';

// Initialisez Stimulus
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!',
    true,
    /\.(j|t)sx?$/
));
