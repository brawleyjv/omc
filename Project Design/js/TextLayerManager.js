const TextLayerManager = {
    addTextLayerButton: document.getElementById('add-text-layer'),

    init() {
        this.addTextLayerButton.addEventListener('click', this.addTextLayer.bind(this));
    },

    addTextLayer() {
        const text = prompt('Enter text:');
        if (text) {
            const div = document.createElement('div');
            div.classList.add('layer', 'text-layer');
            div.textContent = text;
            document.getElementById('preview-area').appendChild(div);
            LayerManager.layers.push(div);
            LayerManager.makeDraggable(div);
        }
    }
};
