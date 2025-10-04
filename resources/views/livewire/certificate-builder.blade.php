<div>
    <input type="text" wire:model="title" placeholder="Certificate Title" class="border p-2 w-full mb-2">

    @if(session()->has('success'))
        <div class="text-green-600 mb-2">{{ session('success') }}</div>
    @endif

    <div id="gjs" style="height:600px; border:1px solid #ccc;"></div>

    <button wire:click="saveCertificate" class="mt-2 bg-blue-500 text-white p-2 rounded">
        Save
    </button>

    @push('styles')
        <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/grapesjs"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', (message, component) => {
                    if (!window.editor) {
                        window.editor = grapesjs.init({
                            container: '#gjs',
                            fromElement: false,
                            height: '600px',
                            width: 'auto',
                            storageManager: { autoload: false },
                        });

                        // Custom Blocks
                        editor.BlockManager.add('name-block', {
                            label: 'Name',
                            content: '<div class="name" style="font-size:20px;">Name Here</div>',
                            category: 'Text'
                        });

                        editor.BlockManager.add('date-block', {
                            label: 'Date',
                            content: '<div class="date" style="font-size:16px;">Date Here</div>',
                            category: 'Text'
                        });

                        editor.BlockManager.add('logo-block', {
                            label: 'Logo',
                            content: '<div class="logo"><img src="https://via.placeholder.com/100"/></div>',
                            category: 'Media'
                        });
                    }
                });

                const saveBtn = document.querySelector('[wire\\:click="saveCertificate"]');
                saveBtn.addEventListener('click', () => {
                    @this.set('html_content', window.editor.getHtml());
                    @this.set('json_content', JSON.stringify(window.editor.getComponents()));
                });
            });
        </script>
    @endpush
</div>
