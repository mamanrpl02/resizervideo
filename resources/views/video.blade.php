<!DOCTYPE html>
<html>

<head>
    <title>Video Resizer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">

    <div class="max-w-xl mx-auto bg-white shadow-lg p-6 rounded-xl">
        <h1 class="text-2xl font-bold mb-4">Resize Video Tool</h1>

        <form id="resizeForm" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="font-semibold">Upload Video</label>
                <input type="file" name="video" required class="block mt-1">
            </div>

            <div>
                <label class="font-semibold">Pilih Kualitas</label>
                <select name="percentage" class="block mt-1 border rounded p-2">

                    <option value="25">Kecil (Cepat & Hemat) · Cocok untuk share cepat</option>
                    <option value="50">Sedang (Seimbang) · Kualitas lumayan, ukuran kecil</option>
                    <option value="75">Besar (Hampir Asli) · Kualitas bagus</option>
                    <option value="100">Kualitas Asli (Tidak diubah)</option>

                    <option value="30">Optimalkan untuk Messenger</option>
                    <option value="40">Optimalkan untuk Email</option>

                </select>
            </div>

            <div>
                <label class="font-semibold">Watermark</label>
                <select name="watermark" class="block mt-1 border rounded p-2">
                    <option value="0">Tanpa Watermark</option>
                    <option value="1">Aktifkan Watermark</option>
                </select>
            </div>


            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Resize Video
            </button>
        </form>

        <!-- Progress -->
        <div id="progressWrapper" class="mt-4 hidden">
            <p class="text-sm font-semibold mb-1">Processing...</p>
            <div class="w-full bg-gray-300 rounded-full h-4">
                <div id="progressBar" class="h-4 bg-blue-600 rounded-full" style="width: 0%"></div>
            </div>
            <p id="progressText" class="text-sm mt-1">0%</p>
        </div>

        <!-- Download -->
        <a id="downloadLink"
            class="hidden mt-4 block bg-green-600 text-white px-4 py-2 text-center rounded-lg hover:bg-green-700">
            Download Video
        </a>
    </div>

    <script>
        document.getElementById('resizeForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            let form = document.getElementById('resizeForm');
            let data = new FormData(form);

            let progressWrapper = document.getElementById('progressWrapper');
            let progressBar = document.getElementById('progressBar');
            let progressText = document.getElementById('progressText');
            let downloadLink = document.getElementById('downloadLink');

            progressWrapper.classList.remove('hidden');
            progressBar.style.width = "0%";
            progressText.textContent = "0%";
            downloadLink.classList.add('hidden');

            // Simulasi loading
            let fakeProgress = 0;
            let interval = setInterval(() => {
                if (fakeProgress < 95) {
                    fakeProgress += 1;
                    progressBar.style.width = fakeProgress + "%";
                    progressText.textContent = fakeProgress + "%";
                }
            }, 100);

            let response = await fetch("{{ route('resize') }}", {
                method: "POST",
                body: data
            });

            clearInterval(interval);
            progressBar.style.width = "100%";
            progressText.textContent = "100%";

            let blob = await response.blob();
            let url = window.URL.createObjectURL(blob);


            const now = new Date();
            const timestamp =
                now.getFullYear().toString() +
                String(now.getMonth() + 1).padStart(2, '0') +
                String(now.getDate()).padStart(2, '0') + "_" +
                String(now.getHours()).padStart(2, '0') +
                String(now.getMinutes()).padStart(2, '0') +
                String(now.getSeconds()).padStart(2, '0');

            downloadLink.download = `manz_resized_video_${timestamp}.mp4`;
            downloadLink.href = url;
            downloadLink.classList.remove('hidden');
        });
    </script>

</body>

</html>
