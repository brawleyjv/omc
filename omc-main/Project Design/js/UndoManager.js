const UndoManager = {
    undoButton: document.getElementById('undo'),

    init() {
        this.undoButton.addEventListener('click', this.undo.bind(this));
    },

    undo() {
        if (LayerManager.layers.length > 0) {
            const lastLayer = LayerManager.layers.pop();
            document.getElementById('preview-area').removeChild(lastLayer);
        }
    }
};
