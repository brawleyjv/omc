const CategoryManager = {
    categorySelect: document.getElementById('category'),
    imageTable: document.getElementById('image-table'),

    init() {
        this.categorySelect.addEventListener('change', this.updateImageTable.bind(this));
        this.updateImageTable();
    },

    updateImageTable() {
        const category = this.categorySelect.value;
        const categoryPath = Config.paths[category.toLowerCase()];
        this.imageTable.innerHTML = '';
        fetch(`getImages.php?category=${categoryPath}`)
            .then(response => response.json())
            .then(images => {
                if (images.error) {
                    console.error(images.error);
                    alert('Error fetching images: ' + images.error);
                    return;
                }
                console.log('Images:', images); // Debugging log
                const table = document.createElement('table');
                table.classList.add('image-table');
                const tbody = document.createElement('tbody');
                let row = document.createElement('tr');
                images.forEach((image, index) => {
                    const cell = document.createElement('td');
                    cell.classList.add('image-cell');
                    const img = document.createElement('img');
                    img.src = `${Config.basePath}${categoryPath}${image}`;
                    img.alt = image;
                    img.addEventListener('click', () => {
                        console.log(`Selected image: ${Config.basePath}${categoryPath}${image}`); // Debugging log
                    });
                    const caption = document.createElement('div');
                    caption.textContent = image;
                    cell.appendChild(img);
                    cell.appendChild(caption);
                    row.appendChild(cell);
                    if ((index + 1) % 3 === 0) {
                        tbody.appendChild(row);
                        row = document.createElement('tr');
                    }
                });
                if (row.children.length > 0) {
                    tbody.appendChild(row);
                }
                table.appendChild(tbody);
                this.imageTable.appendChild(table);
            })
            .catch(error => {
                console.error('Error fetching images:', error);
                alert('Error fetching images: ' + error.message);
            });
    }
};
