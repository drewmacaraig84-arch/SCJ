import { startTunnel } from 'untun';

async function main() {
    try {
        console.log('Starting Cloudflare tunnel on port 8000...');
        const tunnel = await startTunnel({ port: 8000 });
        const url = await tunnel.getURL();
        console.log('========================================================');
        console.log('CLOUDFLARE PUBLIC URL: ' + url);
        console.log('========================================================');
    } catch (err) {
        console.error('Tunnel error:', err);
    }
}

main();
