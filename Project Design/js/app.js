document.addEventListener('DOMContentLoaded', function() {
    const App = {
        init() {
            LayerManager.init();
            TextLayerManager.init();
            UndoManager.init();
        }
    };

    App.init();
});
