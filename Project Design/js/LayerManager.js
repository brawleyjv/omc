const LayerManager = {
    layers: [],
    addLayerButton: document.getElementById('add-layer'),

    init() {
        this.addLayerButton.addEventListener('click', this.addLayer.bind(this));
    },

    addLayer() {
        const imageSrc = ImagePreview.imageSelect.value;
        console.log(`Adding layer: ${imageSrc}`); // Debugging log
        const img = document.createElement('img');
        img.src = imageSrc;
        img.classList.add('layer');
        ImagePreview.previewArea.appendChild(img);
        this.layers.push(img);
        this.makeDraggable(img);
    },

    makeDraggable(element) {
        element.onmousedown = function(event) {
            let shiftX = event.clientX - element.getBoundingClientRect().left;
            let shiftY = event.clientY - element.getBoundingClientRect().top;

            function moveAt(pageX, pageY) {
                element.style.left = pageX - shiftX + 'px';
                element.style.top = pageY - shiftY + 'px';
            }

            function onMouseMove(event) {
                moveAt(event.pageX, event.pageY);
            }

            document.addEventListener('mousemove', onMouseMove);

            element.onmouseup = function() {
                document.removeEventListener('mousemove', onMouseMove);
                element.onmouseup = null;
            };
        };

        element.ondragstart = function() {
            return false;
        };
    }
};
