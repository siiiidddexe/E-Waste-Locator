<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Waste Locator - AI Powered</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Chart.js for analytics visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'emerald': {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .leaflet-container {
            height: 100%;
            width: 100%;
            border-radius: 1.5rem;
        }
        /* Custom CSS for filters */
        .filter-bubble {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .filter-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .filter-toggle {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .filter-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
        }
        
        .range-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            outline: none;
        }
        
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #10b981;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .range-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #10b981;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .filter-tag {
            background: linear-gradient(135deg, #10b981, #059669);
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .facility-card {
            transition: all 0.2s ease;
        }
        
        .facility-card:hover {
            transform: translateY(-2px);
        }
        
        .filter-section {
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }
        
        .filter-section:last-child {
            border-bottom: none;
        }
        
        /* Tab styles */
        .tab-button {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tab-button.active {
            color: #10b981;
            border-bottom: 3px solid #10b981;
        }
        
        .tab-button:not(.active):hover {
            color: #059669;
            background: rgba(16, 185, 129, 0.05);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease-in;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Analytics cards */
        .analytics-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }
        
        .analytics-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2);
        }
        
        .metric-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .spinner {
            border: 3px solid rgba(16, 185, 129, 0.3);
            border-top: 3px solid #10b981;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .model-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .model-card:hover {
            transform: translateX(4px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 via-blue-50 to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-lg border-b border-emerald-100 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-xl flex items-center justify-center">
                    <span class="text-xl">♻️</span>
                </div>
                <div class="text-center">
                    <h1 class="text-xl font-bold text-gray-800">E-Waste Locator</h1>
                    <p class="text-sm text-gray-600">AI-Powered • No Billing Required</p>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <!-- Camera Scan Card -->
        <div class="bg-white/70 backdrop-blur-sm rounded-3xl shadow-lg border border-white/50 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2">
                <span>📸</span>
                <span>Scan E-Waste Items</span>
            </h2>
            
            <!-- Camera Controls -->
            <div class="space-y-4">
                <div class="flex gap-3">
                    <button id="openCameraBtn" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-[0.98] shadow-lg">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Open Camera</span>
                        </div>
                    </button>
                    <button id="uploadImageBtn" class="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-[0.98] shadow-lg">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Upload Image</span>
                        </div>
                    </button>
                </div>
                <input type="file" id="fileInput" accept="image/*" class="hidden">
                
                <!-- Camera Preview -->
                <div id="cameraSection" class="hidden">
                    <div class="relative bg-black rounded-xl overflow-hidden">
                        <video id="cameraStream" class="w-full h-64 object-cover" autoplay playsinline></video>
                        <canvas id="canvas" class="hidden"></canvas>
                        <button id="captureBtn" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white text-gray-800 font-semibold py-3 px-6 rounded-full shadow-lg hover:bg-gray-100 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            </svg>
                        </button>
                        <button id="closeCameraBtn" class="absolute top-4 right-4 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Captured Image Preview -->
                <div id="imagePreview" class="hidden">
                    <div class="relative">
                        <img id="capturedImage" class="w-full h-64 object-cover rounded-xl" alt="Captured">
                        <button id="retakeBtn" class="absolute top-4 right-4 bg-red-500 text-white py-2 px-4 rounded-lg shadow-lg hover:bg-red-600 transition-all">
                            Retake
                        </button>
                    </div>
                </div>
                
                <!-- AI Analysis Results with Tabs -->
                <div id="aiResults" class="hidden">
                    <!-- Tab Navigation -->
                    <div class="bg-white rounded-t-xl overflow-hidden border-b-2 border-gray-200">
                        <div class="flex">
                            <button class="tab-button active flex-1 py-4 px-4 font-semibold text-gray-600 text-center" data-tab="gemini">
                                <div class="flex items-center justify-center space-x-2">
                                    <span>🔷</span>
                                    <span>Gemini</span>
                                </div>
                            </button>
                            <button class="tab-button flex-1 py-4 px-4 font-semibold text-gray-600 text-center" data-tab="openai">
                                <div class="flex items-center justify-center space-x-2">
                                    <span>🤖</span>
                                    <span>OpenAI</span>
                                </div>
                            </button>
                            <button class="tab-button flex-1 py-4 px-4 font-semibold text-gray-600 text-center" data-tab="groq">
                                <div class="flex items-center justify-center space-x-2">
                                    <span>🟢</span>
                                    <span>Groq</span>
                                </div>
                            </button>
                            <button class="tab-button flex-1 py-4 px-4 font-semibold text-gray-600 text-center" data-tab="analysis">
                                <div class="flex items-center justify-center space-x-2">
                                    <span>📊</span>
                                    <span>Analysis</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tab Contents -->
                    <div class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-b-xl p-6">
                        <!-- Gemini Tab -->
                        <div class="tab-content active" id="gemini-tab">
                            <div class="model-card bg-white rounded-xl p-5 shadow-md border-blue-500">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800 flex items-center space-x-2">
                                        <span>🔷</span>
                                        <span>Google Gemini 2.0 Flash</span>
                                    </h3>
                                    <div id="gemini-status" class="text-sm"></div>
                                </div>
                                <div id="gemini-results" class="space-y-3"></div>
                            </div>
                        </div>
                        
                        <!-- OpenAI Tab -->
                        <div class="tab-content" id="openai-tab">
                            <div class="model-card bg-white rounded-xl p-5 shadow-md border-orange-500">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800 flex items-center space-x-2">
                                        <span>🤖</span>
                                        <span>OpenAI GPT-4o Mini</span>
                                    </h3>
                                    <div id="openai-status" class="text-sm"></div>
                                </div>
                                <div id="openai-results" class="space-y-3"></div>
                            </div>
                        </div>
                        
                        <!-- Groq Tab -->
                        <div class="tab-content" id="groq-tab">
                            <div class="model-card bg-white rounded-xl p-5 shadow-md border-green-500">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-800 flex items-center space-x-2">
                                        <span>🟢</span>
                                        <span>Llama 4 Scout 17B (Groq)</span>
                                    </h3>
                                    <div id="groq-status" class="text-sm"></div>
                                </div>
                                <div id="groq-results" class="space-y-3"></div>
                            </div>
                        </div>
                        
                        <!-- Analysis Tab -->
                        <div class="tab-content" id="analysis-tab">
                            <div class="space-y-6">
                                <!-- Performance Overview -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="analytics-card rounded-xl p-5 shadow-lg">
                                        <div class="text-sm text-gray-600 mb-1">Fastest Model</div>
                                        <div id="fastest-model" class="text-2xl font-bold text-emerald-600">-</div>
                                        <div id="fastest-time" class="text-sm text-gray-500 mt-1">-</div>
                                    </div>
                                    <div class="analytics-card rounded-xl p-5 shadow-lg">
                                        <div class="text-sm text-gray-600 mb-1">Most Items Detected</div>
                                        <div id="most-items-model" class="text-2xl font-bold text-blue-600">-</div>
                                        <div id="most-items-count" class="text-sm text-gray-500 mt-1">-</div>
                                    </div>
                                    <div class="analytics-card rounded-xl p-5 shadow-lg">
                                        <div class="text-sm text-gray-600 mb-1">Best Efficiency</div>
                                        <div id="best-efficiency" class="text-2xl font-bold text-purple-600">-</div>
                                        <div id="efficiency-score" class="text-sm text-gray-500 mt-1">-</div>
                                    </div>
                                </div>
                                
                                <!-- Timing Comparison Chart -->
                                <div class="analytics-card rounded-xl p-5 shadow-lg">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2">
                                        <span>⚡</span>
                                        <span>Response Time Comparison</span>
                                    </h4>
                                    <canvas id="timingChart" height="80"></canvas>
                                </div>
                                
                                <!-- Detailed Metrics -->
                                <div class="analytics-card rounded-xl p-5 shadow-lg">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2">
                                        <span>📈</span>
                                        <span>Detailed Performance Metrics</span>
                                    </h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b-2 border-gray-200">
                                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Model</th>
                                                    <th class="text-center py-3 px-2 font-semibold text-gray-700">Time (ms)</th>
                                                    <th class="text-center py-3 px-2 font-semibold text-gray-700">Items</th>
                                                    <th class="text-center py-3 px-2 font-semibold text-gray-700">Text Length</th>
                                                    <th class="text-center py-3 px-2 font-semibold text-gray-700">Score</th>
                                                </tr>
                                            </thead>
                                            <tbody id="metricsTable">
                                                <tr><td colspan="5" class="text-center py-4 text-gray-500">Analyzing...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- System Resources -->
                                <div class="analytics-card rounded-xl p-5 shadow-lg">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2">
                                        <span>💾</span>
                                        <span>System Resource Usage</span>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <canvas id="resourceChart" height="120"></canvas>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="text-xs text-gray-600 mb-1">Memory Usage</div>
                                                <div id="memory-usage" class="text-lg font-bold text-blue-600">-</div>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="text-xs text-gray-600 mb-1">Network Data</div>
                                                <div id="network-usage" class="text-lg font-bold text-purple-600">-</div>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="text-xs text-gray-600 mb-1">Total Processing Time</div>
                                                <div id="total-time" class="text-lg font-bold text-emerald-600">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cost Comparison -->
                                <div class="analytics-card rounded-xl p-5 shadow-lg">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center space-x-2">
                                        <span>💰</span>
                                        <span>Cost Efficiency Analysis</span>
                                    </h4>
                                    <canvas id="costChart" height="80"></canvas>
                                    <div class="mt-4 text-xs text-green-600 font-semibold">
                                        🎉 All three APIs offer free tiers! Gemini: 1,500/day | OpenAI: $5 trial credit | Groq: 14,400/day
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Analyze Button -->
                <button id="analyzeBtn" class="hidden w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-[0.98] shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span>Analyze with AI</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Location Card -->
        <div class="bg-white/70 backdrop-blur-sm rounded-3xl shadow-lg border border-white/50 p-6">
            <button id="locationBtn" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 transform hover:scale-[0.98] active:scale-95 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Find E-Waste Centers Near Me</span>
                </div>
            </button>
            
            <!-- Status Message -->
            <div id="status" class="mt-4 hidden">
                <div class="flex items-center justify-center space-x-3 p-4 rounded-2xl">
                    <div id="statusIcon"></div>
                    <span id="statusText" class="font-medium"></span>
                </div>
            </div>
            
            <!-- Active Filters -->
            <div id="activeFilters" class="mt-4 hidden">
                <div class="flex flex-wrap gap-2" id="filterTags"></div>
            </div>
        </div>

        <!-- Map Card -->
        <div class="bg-white/70 backdrop-blur-sm rounded-3xl shadow-lg border border-white/50 overflow-hidden">
            <div class="h-80 md:h-96" id="map"></div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Nearby Facilities</h2>
                <div id="resultCount" class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-medium">
                    0 Results
                </div>
            </div>
            
            <div id="facilityList" class="space-y-4"></div>
        </div>
    </div>

    <!-- Filter Toggle Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <button id="filterToggle" class="filter-toggle w-14 h-14 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-full shadow-lg flex items-center justify-center hover:shadow-2xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
            </svg>
        </button>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="fixed inset-y-0 right-0 z-50 w-80 transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="filter-panel h-full rounded-l-3xl overflow-y-auto">
            <!-- Filter Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Filters</h3>
                    <button id="closeFilter" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Filter Content -->
            <div class="p-6 space-y-6">
                <!-- Distance Filter -->
                <div class="filter-section pb-6">
                    <h4 class="font-medium text-gray-800 mb-3">Distance Range</h4>
                    <div class="space-y-3">
                        <input type="range" id="distanceRange" min="1" max="20" value="5" class="range-slider">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>1 km</span>
                            <span id="distanceValue" class="font-medium text-emerald-600">5 km</span>
                            <span>20 km</span>
                        </div>
                    </div>
                </div>

                <!-- Rating Filter -->
                <div class="filter-section pb-6">
                    <h4 class="font-medium text-gray-800 mb-3">Minimum Rating</h4>
                    <div class="space-y-3">
                        <input type="range" id="ratingRange" min="0" max="5" step="0.5" value="0" class="range-slider">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Any</span>
                            <span id="ratingValue" class="font-medium text-emerald-600">Any Rating</span>
                            <span>5 ⭐</span>
                        </div>
                    </div>
                </div>

                <!-- Opening Hours Filter -->
                <div class="filter-section pb-6">
                    <h4 class="font-medium text-gray-800 mb-3">Opening Status</h4>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="openStatus" value="all" checked class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-500">
                            <span class="text-gray-700">Show All</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="openStatus" value="open" class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-500">
                            <span class="text-gray-700">Open Now Only</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="openStatus" value="closed" class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-500">
                            <span class="text-gray-700">Closed Now Only</span>
                        </label>
                    </div>
                </div>

                <!-- Price Level Filter -->
                <div class="filter-section pb-6">
                    <h4 class="font-medium text-gray-800 mb-3">Price Level</h4>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" value="0" class="price-level w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-gray-700">Free</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" value="1" class="price-level w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-gray-700">$ - Inexpensive</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" value="2" class="price-level w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-gray-700">$$ - Moderate</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" value="3" class="price-level w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-gray-700">$$$ - Expensive</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" value="4" class="price-level w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-gray-700">$$$$ - Very Expensive</span>
                        </label>
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="filter-section pb-6">
                    <h4 class="font-medium text-gray-800 mb-3">Sort By</h4>
                    <select id="sortBy" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="distance">Distance (Nearest First)</option>
                        <option value="rating">Rating (Highest First)</option>
                        <option value="name">Name (A-Z)</option>
                    </select>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="p-6 border-t border-gray-200">
                <div class="flex space-x-3">
                    <button id="clearFilters" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-4 rounded-xl transition-colors">
                        Clear All
                    </button>
                    <button id="applyFilters" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-medium py-3 px-4 rounded-xl transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Facility Detail Modal -->
    <div id="facilityModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-t-3xl sm:rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-xl font-semibold text-gray-800"></h3>
                        <button class="close-modal w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-shadow">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Content -->
                <div id="modalContent" class="px-6 py-6 space-y-4"></div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4">
                    <button class="close-modal w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-xl transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        let map;
        let userLocation;
        let markers = [];
        let allFacilities = [];
        let filteredFacilities = [];
        let cameraStream = null;
        let detectedEWasteItems = [];
        
        // ==================== API KEYS CONFIGURATION ====================
        // All three services offer FREE tiers! Replace with your own API keys:
        // 
        // 1. GEMINI (Google) - FREE ✅
        //    Get from: https://aistudio.google.com/apikey
        //    Free tier: 15 requests/min, 1,500 requests/day
        //    Model: gemini-2.0-flash-exp (experimental, supports vision)
        //
        // 2. OPENAI - FREE TRIAL ✅  
        //    Get from: https://platform.openai.com/api-keys
        //    Free trial: $5 credit for new accounts
        //    Model: gpt-4o-mini (vision-capable, fast, affordable)
        //
        // 3. GROQ - FREE ✅
        //    Get from: https://console.groq.com/keys
        //    Free tier: 1,000 requests/min, 14,400 requests/day
        //    Model: meta-llama/llama-4-scout-17b-16e-instruct
        //    Features: Vision-capable, ultra-fast inference (750 tokens/sec)
        // ================================================================
        
        const GEMINI_API_KEY = 'AIzaSyA7Voj3Of9T5CgRING50P5OqCZVonnob_s';
        const OPENAI_API_KEY = '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'; // Get from https://platform.openai.com/api-keys
        const GROQ_API_KEY = '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'; // Get from https://console.groq.com/
        
        // Model analysis results storage
        let modelResults = {
            gemini: { items: [], time: 0, text: '', status: 'pending', error: null },
            openai: { items: [], time: 0, text: '', status: 'pending', error: null },
            groq: { items: [], time: 0, text: '', status: 'pending', error: null }
        };
        
        // System resource tracking
        let resourceMetrics = {
            startMemory: 0,
            endMemory: 0,
            networkData: 0,
            startTime: 0,
            endTime: 0
        };
        
        // Filter state
        const filters = {
            maxDistance: 5,
            minRating: 0,
            openStatus: 'all',
            priceLevels: [],
            sortBy: 'distance'
        };
        
        // Initialize OpenStreetMap
        function initMap() {
            const defaultLocation = [28.6139, 77.2090]; // Delhi
            
            map = L.map('map').setView(defaultLocation, 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
        }
        
        // Get user's current location
        function getUserLocation() {
            const statusDiv = document.getElementById('status');
            const statusIcon = document.getElementById('statusIcon');
            const statusText = document.getElementById('statusText');
            const locationBtn = document.getElementById('locationBtn');
            
            statusDiv.style.display = 'block';
            statusDiv.className = 'mt-4 bg-blue-50 text-blue-700 rounded-2xl';
            statusIcon.innerHTML = `
                <div class="animate-spin w-5 h-5 border-2 border-blue-300 border-t-blue-600 rounded-full"></div>
            `;
            statusText.textContent = 'Getting your location...';
            locationBtn.disabled = true;
            
            if (!navigator.geolocation) {
                showStatus('error', 'Location not supported by browser');
                locationBtn.disabled = false;
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    map.setView([userLocation.lat, userLocation.lng], 15);
                    
                    // Add user location marker
                    L.marker([userLocation.lat, userLocation.lng], {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: "<div style='background-color:#10b981;width:24px;height:24px;border-radius:50%;border:4px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)'></div>",
                            iconSize: [24, 24],
                            iconAnchor: [12, 12]
                        })
                    }).addTo(map).bindPopup('<b>📍 Your Location</b>').openPopup();
                    
                    showStatus('success', 'Searching for facilities...');
                    searchEWasteFacilities();
                },
                (error) => {
                    let errorMessage = 'Location access denied';
                    if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Location unavailable';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Location request timeout';
                    }
                    showStatus('error', errorMessage);
                    locationBtn.disabled = false;
                }
            );
        }
        
        // Search for e-waste facilities using Overpass API
        async function searchEWasteFacilities() {
            if (!userLocation) return;
            
            showStatus('info', '🔍 Searching for e-waste facilities...');
            clearMarkers();
            allFacilities = [];
            
            // Overpass API query for e-waste facilities
            const query = `
                [out:json][timeout:25];
                (
                  node["recycling:waste_electrical_equipment"="yes"](around:10000,${userLocation.lat},${userLocation.lng});
                  node["amenity"="recycling"](around:10000,${userLocation.lat},${userLocation.lng});
                  node["shop"="electronics"]["repair"="yes"](around:10000,${userLocation.lat},${userLocation.lng});
                  node["shop"="computer"]["repair"="yes"](around:10000,${userLocation.lat},${userLocation.lng});
                  way["amenity"="recycling"](around:10000,${userLocation.lat},${userLocation.lng});
                );
                out center;
            `;
            
            try {
                const response = await fetch('https://overpass-api.de/api/interpreter', {
                    method: 'POST',
                    body: 'data=' + encodeURIComponent(query)
                });
                
                const data = await response.json();
                
                if (data.elements && data.elements.length > 0) {
                    processFacilities(data.elements);
                } else {
                    // Generate demo facilities if no real ones found
                    generateDemoFacilities();
                }
            } catch (error) {
                console.error('Error fetching facilities:', error);
                showStatus('info', 'ℹ️ Generating demo facilities...');
                generateDemoFacilities();
            }
        }
        
        // Generate demo facilities with realistic Indian data
        function generateDemoFacilities() {
            const indianCities = [
                { name: 'Delhi', areas: ['Connaught Place', 'Karol Bagh', 'Lajpat Nagar', 'Nehru Place', 'Dwarka'] },
                { name: 'Mumbai', areas: ['Andheri', 'Bandra', 'Powai', 'Worli', 'Dadar'] },
                { name: 'Bangalore', areas: ['Koramangala', 'Indiranagar', 'Whitefield', 'Jayanagar', 'Electronic City'] },
                { name: 'Hyderabad', areas: ['Hitech City', 'Banjara Hills', 'Madhapur', 'Kukatpally', 'Ameerpet'] },
                { name: 'Pune', areas: ['Hinjewadi', 'Koregaon Park', 'Viman Nagar', 'Kothrud', 'Aundh'] }
            ];
            
            const demoNames = [
                'Green E-Waste Recycling Center',
                'EcoTech Electronics Disposal',
                'Digital Waste Solutions Hub',
                'Tech Recycle & Repair Center',
                'E-Waste Collection Point',
                'Urban Electronics Recycling',
                'Smart Recycle Facility',
                'Clean Tech Disposal Center',
                'Electronic Waste Management Co.',
                'Sustainable E-Waste Solutions'
            ];
            
            const streetNames = [
                'MG Road', 'Main Road', 'Link Road', 'Service Road', 'Ring Road',
                'Station Road', 'Market Street', 'Industrial Area', 'Sector Road', 'Park Street'
            ];
            
            const demoFacilities = [];
            const numFacilities = Math.min(10, demoNames.length);
            
            // Detect if user is in India
            const isInIndia = userLocation.lat > 8 && userLocation.lat < 35 && 
                             userLocation.lng > 68 && userLocation.lng < 97;
            
            let cityData = indianCities[0]; // Default to Delhi
            if (isInIndia) {
                // Find closest Indian city
                const cityCoords = {
                    'Delhi': {lat: 28.6139, lng: 77.2090},
                    'Mumbai': {lat: 19.0760, lng: 72.8777},
                    'Bangalore': {lat: 12.9716, lng: 77.5946},
                    'Hyderabad': {lat: 17.3850, lng: 78.4867},
                    'Pune': {lat: 18.5204, lng: 73.8567}
                };
                
                let closestCity = 'Delhi';
                let minDistance = Infinity;
                
                Object.entries(cityCoords).forEach(([city, coords]) => {
                    const dist = calculateDistance(userLocation.lat, userLocation.lng, coords.lat, coords.lng);
                    if (dist < minDistance) {
                        minDistance = dist;
                        closestCity = city;
                    }
                });
                
                cityData = indianCities.find(c => c.name === closestCity) || indianCities[0];
            }
            
            for (let i = 0; i < numFacilities; i++) {
                // Generate random location within 10km radius
                const angle = Math.random() * 2 * Math.PI;
                const radius = (0.02 + Math.random() * 0.08); // 2-10km
                
                const randomOffset = {
                    lat: radius * Math.cos(angle),
                    lng: radius * Math.sin(angle)
                };
                
                const area = cityData.areas[i % cityData.areas.length];
                const street = streetNames[Math.floor(Math.random() * streetNames.length)];
                const building = `${10 + i * 15}, ${street}`;
                
                // Indian phone numbers
                const phoneFormats = [
                    `+91-${7 + Math.floor(Math.random() * 3)}${Math.floor(Math.random() * 1000000000).toString().padStart(9, '0')}`,
                    `+91 ${Math.floor(Math.random() * 90000 + 10000)} ${Math.floor(Math.random() * 90000 + 10000)}`
                ];
                
                demoFacilities.push({
                    id: i,
                    lat: userLocation.lat + randomOffset.lat,
                    lon: userLocation.lng + randomOffset.lng,
                    tags: {
                        name: demoNames[i],
                        'addr:street': building,
                        'addr:area': area,
                        'addr:city': cityData.name,
                        'addr:state': cityData.name === 'Delhi' ? 'Delhi' : 
                                     cityData.name === 'Mumbai' ? 'Maharashtra' :
                                     cityData.name === 'Bangalore' ? 'Karnataka' :
                                     cityData.name === 'Hyderabad' ? 'Telangana' : 'Maharashtra',
                        'addr:postcode': `${110000 + i * 1000}`,
                        phone: phoneFormats[Math.floor(Math.random() * phoneFormats.length)],
                        opening_hours: i % 3 === 0 ? 'Mo-Fr 09:00-18:00' : i % 3 === 1 ? 'Mo-Sa 10:00-17:00' : 'Mo-Su 09:00-20:00',
                        website: i % 2 === 0 ? `https://ewaste-${cityData.name.toLowerCase()}-${i}.in` : null,
                        email: `contact@${demoNames[i].toLowerCase().replace(/\s+/g, '')}.in`,
                        operator: i % 4 === 0 ? 'Government Authorized' : 'Private Licensed'
                    }
                });
            }
            
            processFacilities(demoFacilities);
        }
        
        // Process and store facilities data
        function processFacilities(facilities) {
            // Remove duplicates
            const uniqueFacilities = facilities.filter((facility, index, self) =>
                index === self.findIndex(f => f.id === facility.id)
            );
            
            allFacilities = uniqueFacilities.map(facility => {
                // Get coordinates (handle both node and way types)
                const lat = facility.lat || facility.center?.lat;
                const lon = facility.lon || facility.center?.lon;
                
                const distance = calculateDistance(
                    userLocation.lat, userLocation.lng,
                    lat, lon
                );
                
                // Randomly assign e-waste capacity level
                const capacityLevels = [
                    {
                        level: 'Small',
                        description: 'Accepts: Mobile phones, batteries, small electronics',
                        color: 'text-blue-600 bg-blue-50',
                        icon: '📱'
                    },
                    {
                        level: 'Medium',
                        description: 'Accepts: Laptops, tablets, monitors, printers',
                        color: 'text-yellow-600 bg-yellow-50',
                        icon: '💻'
                    },
                    {
                        level: 'Large',
                        description: 'Accepts: All electronics, appliances, industrial e-waste',
                        color: 'text-green-600 bg-green-50',
                        icon: '🏭'
                    }
                ];
                
                const capacity = capacityLevels[Math.floor(Math.random() * 3)];
                
                // Generate rating between 3.5 and 5.0
                const rating = (3.5 + Math.random() * 1.5).toFixed(1);
                
                // Random opening status
                const isOpen = Math.random() > 0.3;
                
                // Format address properly
                const addressParts = [];
                if (facility.tags?.['addr:street']) addressParts.push(facility.tags['addr:street']);
                if (facility.tags?.['addr:area']) addressParts.push(facility.tags['addr:area']);
                if (facility.tags?.['addr:city']) addressParts.push(facility.tags['addr:city']);
                if (facility.tags?.['addr:state']) addressParts.push(facility.tags['addr:state']);
                if (facility.tags?.['addr:postcode']) addressParts.push(facility.tags['addr:postcode']);
                
                const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : 'Address not available';
                
                return {
                    ...facility,
                    lat,
                    lon,
                    distance,
                    capacity,
                    rating: parseFloat(rating),
                    price_level: Math.floor(Math.random() * 3),
                    is_open: isOpen,
                    name: facility.tags?.name || `E-Waste Facility ${facility.id}`,
                    vicinity: `${facility.tags?.['addr:street'] || ''}, ${facility.tags?.['addr:area'] || ''}, ${facility.tags?.['addr:city'] || 'City'}`.replace(/^,\s*/, '').replace(/,\s*,/g, ','),
                    fullAddress: fullAddress,
                    phone: facility.tags?.phone || 'N/A',
                    website: facility.tags?.website || null,
                    opening_hours: facility.tags?.opening_hours || 'Call for hours',
                    email: facility.tags?.email || null,
                    operator: facility.tags?.operator || null
                };
            });
            
            // If we have detected e-waste items, sort facilities by capacity
            if (detectedEWasteItems.length > 0) {
                filterByEWasteType();
            } else {
                // Default sort by distance
                allFacilities.sort((a, b) => a.distance - b.distance);
            }
            
            applyFiltersAndDisplay();
            showStatus('success', `✅ Found ${allFacilities.length} facilities`);
            document.getElementById('locationBtn').disabled = false;
        }
        
        // Filter facilities based on detected e-waste items
        function filterByEWasteType() {
            // Analyze detected items to determine required capacity
            const hasLargeItems = detectedEWasteItems.some(item => 
                item.toLowerCase().includes('refrigerator') ||
                item.toLowerCase().includes('washing machine') ||
                item.toLowerCase().includes('tv') ||
                item.toLowerCase().includes('air conditioner')
            );
            
            const hasMediumItems = detectedEWasteItems.some(item =>
                item.toLowerCase().includes('laptop') ||
                item.toLowerCase().includes('computer') ||
                item.toLowerCase().includes('monitor') ||
                item.toLowerCase().includes('printer')
            );
            
            // Prioritize facilities with appropriate capacity
            allFacilities = allFacilities.sort((a, b) => {
                if (hasLargeItems && a.capacity.level === 'Large') return -1;
                if (hasLargeItems && b.capacity.level === 'Large') return 1;
                if (hasMediumItems && a.capacity.level === 'Medium') return -1;
                if (hasMediumItems && b.capacity.level === 'Medium') return 1;
                return 0;
            });
        }
        
        // Apply filters and display results
        function applyFiltersAndDisplay() {
            if (allFacilities.length === 0) return;
            
            // Apply filters
            filteredFacilities = allFacilities.filter(facility => {
                // Distance filter
                if (facility.distance > filters.maxDistance) return false;
                
                // Rating filter
                if (filters.minRating > 0 && facility.rating < filters.minRating) return false;
                
                // Opening hours filter
                if (filters.openStatus === 'open' && !facility.is_open) return false;
                if (filters.openStatus === 'closed' && facility.is_open) return false;
                
                // Price level filter
                if (filters.priceLevels.length > 0) {
                    if (facility.price_level === -1) return false;
                    if (!filters.priceLevels.includes(facility.price_level.toString())) return false;
                }
                
                return true;
            });
            
            // Sort facilities
            sortFacilities();
            
            // Display results
            displayFacilities(filteredFacilities);
            updateActiveFilters();
            updateResultCount();
        }
        
        // Sort facilities based on selected criteria
        function sortFacilities() {
            filteredFacilities.sort((a, b) => {
                switch (filters.sortBy) {
                    case 'distance':
                        return a.distance - b.distance;
                    case 'rating':
                        return (b.rating || 0) - (a.rating || 0);
                    case 'name':
                        return a.name.localeCompare(b.name);
                    default:
                        return a.distance - b.distance;
                }
            });
        }
        
        // Display facilities
        function displayFacilities(facilities) {
            clearMarkers();
            
            const facilityList = document.getElementById('facilityList');
            const resultsSection = document.getElementById('resultsSection');
            
            facilityList.innerHTML = '';
            
            if (facilities.length === 0) {
                showNoResults();
                return;
            }
            
            resultsSection.style.display = 'block';
            
            facilities.forEach(facility => {
                // Add marker
                const marker = L.marker([facility.lat, facility.lon], {
                    icon: L.divIcon({
                        className: 'custom-marker-icon',
                        html: `<div style='background-color:#dc2626;width:32px;height:32px;border-radius:50% 50% 50% 0;border:3px solid white;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;'>
                            <div style='transform:rotate(45deg);font-size:16px;'>♻️</div>
                        </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    })
                }).addTo(map);
                
                marker.bindPopup(`
                    <div style="min-width:200px">
                        <b style="font-size:14px">${facility.name}</b><br>
                        <span style="font-size:12px">${facility.capacity.icon} ${facility.capacity.level} Capacity</span><br>
                        <span style="font-size:12px">⭐ ${facility.rating}</span><br>
                        <span style="font-size:12px">📍 ${facility.distance.toFixed(1)} km away</span>
                    </div>
                `);
                
                markers.push(marker);
                marker.on('click', () => showFacilityDetails(facility, facility.distance));
                
                // Create facility card
                const card = document.createElement('div');
                card.className = 'facility-card bg-white/70 backdrop-blur-sm rounded-2xl p-5 shadow-md border border-white/50 hover:shadow-lg cursor-pointer';
                card.onclick = () => showFacilityDetails(facility, facility.distance);
                
                const rating = facility.rating ? facility.rating.toFixed(1) : 'N/A';
                const isOpen = facility.is_open;
                const priceLevel = getPriceLevelText(facility.price_level);
                const capacity = facility.capacity;
                
                card.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 pr-4">
                            <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-1">${facility.name}</h3>
                            <p class="text-gray-600 text-sm leading-relaxed">${facility.vicinity || 'Address not available'}</p>
                        </div>
                        <div class="bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap">
                            ${facility.distance.toFixed(1)} km
                        </div>
                    </div>
                    
                    <!-- E-Waste Capacity Badge -->
                    <div class="mb-3 ${capacity.color} rounded-lg p-3 border-l-4 border-current">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-lg">${capacity.icon}</span>
                            <span class="font-semibold text-sm">${capacity.level} Capacity Vendor</span>
                        </div>
                        <p class="text-xs opacity-90">${capacity.description}</p>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-1 text-amber-500">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">${rating}</span>
                            </div>
                            ${priceLevel ? `
                                <div class="text-sm text-gray-600 font-medium">${priceLevel}</div>
                            ` : ''}
                            ${isOpen !== undefined ? `
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 rounded-full ${isOpen ? 'bg-green-400' : 'bg-red-400'}"></div>
                                    <span class="text-sm text-gray-600">${isOpen ? 'Open' : 'Closed'}</span>
                                </div>
                            ` : ''}
                        </div>
                        
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                `;
                
                facilityList.appendChild(card);
            });
        }
        
        // Clear all markers from map
        function clearMarkers() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
        }
        
        // Get price level text
        function getPriceLevelText(priceLevel, useRupees = true) {
            if (useRupees) {
                switch (priceLevel) {
                    case 0: return 'Free';
                    case 1: return '₹';
                    case 2: return '₹₹';
                    case 3: return '₹₹₹';
                    case 4: return '₹₹₹₹';
                    default: return null;
                }
            } else {
                switch (priceLevel) {
                    case 0: return 'Free';
                    case 1: return '$';
                    case 2: return '$$';
                    case 3: return '$$$';
                    case 4: return '$$$$';
                    default: return null;
                }
            }
        }
        
        // Update active filters display
        function updateActiveFilters() {
            const activeFiltersDiv = document.getElementById('activeFilters');
            const filterTags = document.getElementById('filterTags');
            
            filterTags.innerHTML = '';
            let hasActiveFilters = false;
            
            // Distance filter
            if (filters.maxDistance !== 5) {
                addFilterTag(`Within ${filters.maxDistance} km`, () => {
                    filters.maxDistance = 5;
                    document.getElementById('distanceRange').value = 5;
                    updateDistanceValue();
                    applyFiltersAndDisplay();
                });
                hasActiveFilters = true;
            }
            
            // Rating filter
            if (filters.minRating > 0) {
                addFilterTag(`${filters.minRating}+ stars`, () => {
                    filters.minRating = 0;
                    document.getElementById('ratingRange').value = 0;
                    updateRatingValue();
                    applyFiltersAndDisplay();
                });
                hasActiveFilters = true;
            }
            
            // Opening status filter
            if (filters.openStatus !== 'all') {
                const statusText = filters.openStatus === 'open' ? 'Open Now' : 'Closed Now';
                addFilterTag(statusText, () => {
                    filters.openStatus = 'all';
                    document.querySelector('input[name="openStatus"][value="all"]').checked = true;
                    applyFiltersAndDisplay();
                });
                hasActiveFilters = true;
            }
            
            // Price level filter
            if (filters.priceLevels.length > 0) {
                const priceText = filters.priceLevels.map(level => getPriceLevelText(parseInt(level))).join(', ');
                addFilterTag(`Price: ${priceText}`, () => {
                    filters.priceLevels = [];
                    document.querySelectorAll('.price-level').forEach(cb => cb.checked = false);
                    applyFiltersAndDisplay();
                });
                hasActiveFilters = true;
            }
            
            // Sort filter
            if (filters.sortBy !== 'distance') {
                const sortText = filters.sortBy === 'rating' ? 'By Rating' : 'By Name';
                addFilterTag(`Sorted ${sortText}`, () => {
                    filters.sortBy = 'distance';
                    document.getElementById('sortBy').value = 'distance';
                    applyFiltersAndDisplay();
                });
                hasActiveFilters = true;
            }
            
            activeFiltersDiv.style.display = hasActiveFilters ? 'block' : 'none';
        }
        
        // Add filter tag
        function addFilterTag(text, removeCallback) {
            const filterTags = document.getElementById('filterTags');
            const tag = document.createElement('div');
            tag.className = 'filter-tag inline-flex items-center space-x-2 px-3 py-1 rounded-full text-sm text-white font-medium';
            tag.innerHTML = `
                <span>${text}</span>
                <button class="w-4 h-4 hover:bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            
            tag.querySelector('button').onclick = (e) => {
                e.stopPropagation();
                removeCallback();
            };
            
            filterTags.appendChild(tag);
        }
        
        // Update result count
        function updateResultCount() {
            const resultCount = document.getElementById('resultCount');
            const count = filteredFacilities.length;
            resultCount.textContent = `${count} Result${count !== 1 ? 's' : ''}`;
        }
        
        // Update distance value display
        function updateDistanceValue() {
            const value = document.getElementById('distanceRange').value;
            document.getElementById('distanceValue').textContent = `${value} km`;
        }
        
        // Update rating value display
        function updateRatingValue() {
            const value = parseFloat(document.getElementById('ratingRange').value);
            document.getElementById('ratingValue').textContent = value === 0 ? 'Any Rating' : `${value}+ ⭐`;
        }
        
        // Show facility details modal
        function showFacilityDetails(facility, distance) {
            const modal = document.getElementById('facilityModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.textContent = facility.name;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            const openNow = facility.is_open;
            const priceLevel = getPriceLevelText(facility.price_level);
            const capacity = facility.capacity;
            
            modalContent.innerHTML = `
                        <div class="space-y-4">
                            <!-- E-Waste Capacity Info -->
                            <div class="${capacity.color} rounded-lg p-4 border-l-4 border-current">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-2xl">${capacity.icon}</span>
                                    <span class="font-bold text-lg">${capacity.level} Capacity Vendor</span>
                                </div>
                                <p class="text-sm opacity-90">${capacity.description}</p>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 text-gray-400 mt-0.5">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Address</p>
                                    <p class="text-gray-800">${facility.fullAddress || facility.vicinity || 'Not available'}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 text-gray-400 mt-0.5">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Distance</p>
                                    <p class="text-gray-800">${distance.toFixed(1)} km from your location</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 text-gray-400 mt-0.5">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Location</p>
                                    <p class="text-gray-800 text-sm mb-1">${facility.lat.toFixed(5)}, ${facility.lon.toFixed(5)}</p>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=${facility.lat},${facility.lon}" 
                                       target="_blank" 
                                       class="text-emerald-600 hover:text-emerald-700 text-sm font-medium inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Get Directions
                                    </a>
                                </div>
                            </div>
                            
                            ${facility.phone && facility.phone !== 'N/A' ? `
                                <div class="flex items-start space-x-3">
                                    <div class="w-5 h-5 text-gray-400 mt-0.5">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Phone</p>
                                        <a href="tel:${facility.phone}" class="text-emerald-600 hover:text-emerald-700">${facility.phone}</a>
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${facility.email ? `
                                <div class="flex items-start space-x-3">
                                    <div class="w-5 h-5 text-gray-400 mt-0.5">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Email</p>
                                        <a href="mailto:${facility.email}" class="text-emerald-600 hover:text-emerald-700">${facility.email}</a>
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${facility.operator ? `
                                <div class="flex items-start space-x-3">
                                    <div class="w-5 h-5 text-gray-400 mt-0.5">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Operator</p>
                                        <p class="text-gray-800">${facility.operator}</p>
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${facility.website ? `
                                <div class="flex items-start space-x-3">
                                    <div class="w-5 h-5 text-gray-400 mt-0.5">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Website</p>
                                        <a href="${facility.website}" target="_blank" class="text-emerald-600 hover:text-emerald-700">Visit Website</a>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-5 h-5 text-gray-400 mt-0.5">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Hours</p>
                                    <p class="text-gray-800">${facility.opening_hours}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-2">
                                ${facility.rating ? `
                                    <div class="flex items-center space-x-2">
                                        <div class="flex items-center space-x-1 text-amber-500">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                            <span class="font-semibold text-gray-800">${facility.rating}/5</span>
                                        </div>
                                        ${priceLevel ? `<span class="text-gray-600 font-medium">${priceLevel}</span>` : ''}
                                    </div>
                                ` : ''}
                                
                                ${openNow !== undefined ? `
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full ${openNow ? 'bg-green-400' : 'bg-red-400'}"></div>
                                        <span class="text-sm font-medium ${openNow ? 'text-green-700' : 'text-red-700'}">${openNow ? 'Open Now' : 'Closed'}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
        }
        
        // Helper functions
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }
        
        function showStatus(type, message) {
            const statusDiv = document.getElementById('status');
            const statusIcon = document.getElementById('statusIcon');
            const statusText = document.getElementById('statusText');
            
            statusDiv.style.display = 'block';
            statusText.textContent = message;
            
            if (type === 'success') {
                statusDiv.className = 'mt-4 bg-green-50 text-green-700 rounded-2xl';
                statusIcon.innerHTML = `
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
            } else if (type === 'error') {
                statusDiv.className = 'mt-4 bg-red-50 text-red-700 rounded-2xl';
                statusIcon.innerHTML = `
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                `;
            }
        }
        
        function showNoResults() {
            const facilityList = document.getElementById('facilityList');
            const resultsSection = document.getElementById('resultsSection');
            
            resultsSection.style.display = 'block';
            facilityList.innerHTML = `
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">No facilities found</h3>
                    <p class="text-gray-600">Try adjusting your filters or expanding your search area.</p>
                </div>
            `;
            
            updateResultCount();
        }
        
        // ============ CAMERA AND AI FUNCTIONALITY ============
        
        // Open camera
        document.getElementById('openCameraBtn').addEventListener('click', async () => {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' },
                    audio: false 
                });
                
                const videoElement = document.getElementById('cameraStream');
                videoElement.srcObject = cameraStream;
                
                document.getElementById('cameraSection').classList.remove('hidden');
                document.getElementById('imagePreview').classList.add('hidden');
                document.getElementById('analyzeBtn').classList.add('hidden');
            } catch (error) {
                alert('Unable to access camera. Please check permissions.');
                console.error('Camera error:', error);
            }
        });
        
        // Close camera
        document.getElementById('closeCameraBtn').addEventListener('click', () => {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            document.getElementById('cameraSection').classList.add('hidden');
        });
        
        // Capture photo
        document.getElementById('captureBtn').addEventListener('click', () => {
            const video = document.getElementById('cameraStream');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            
            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Convert to image
            const imageDataUrl = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('capturedImage').src = imageDataUrl;
            
            // Stop camera stream
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            
            // Show preview
            document.getElementById('cameraSection').classList.add('hidden');
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('analyzeBtn').classList.remove('hidden');
        });
        
        // Upload image
        document.getElementById('uploadImageBtn').addEventListener('click', () => {
            document.getElementById('fileInput').click();
        });
        
        document.getElementById('fileInput').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    document.getElementById('capturedImage').src = event.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('analyzeBtn').classList.remove('hidden');
                    document.getElementById('cameraSection').classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Retake photo
        document.getElementById('retakeBtn').addEventListener('click', () => {
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('analyzeBtn').classList.add('hidden');
            document.getElementById('aiResults').classList.add('hidden');
            detectedEWasteItems = [];
        });
        
        // Analyze image with all 3 LLMs in parallel
        document.getElementById('analyzeBtn').addEventListener('click', async () => {
            const analyzeBtn = document.getElementById('analyzeBtn');
            analyzeBtn.disabled = true;
            analyzeBtn.innerHTML = `
                <div class="flex items-center justify-center space-x-2">
                    <div class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></div>
                    <span>Analyzing with 3 AI Models...</span>
                </div>
            `;
            
            // Reset results
            modelResults = {
                gemini: { items: [], time: 0, text: '', status: 'analyzing', error: null },
                openai: { items: [], time: 0, text: '', status: 'analyzing', error: null },
                groq: { items: [], time: 0, text: '', status: 'analyzing', error: null }
            };
            
            // Start resource tracking
            resourceMetrics.startTime = performance.now();
            if (performance.memory) {
                resourceMetrics.startMemory = performance.memory.usedJSHeapSize;
            }
            resourceMetrics.networkData = 0;
            
            document.getElementById('aiResults').classList.remove('hidden');
            
            // Show analyzing status
            updateModelStatus('gemini', 'Analyzing...');
            updateModelStatus('openai', 'Analyzing...');
            updateModelStatus('groq', 'Analyzing...');
            
            const imageElement = document.getElementById('capturedImage');
            const imageData = imageElement.src;
            const base64Image = imageData.split(',')[1];
            
            // Call all 3 APIs in parallel
            console.log('Starting analysis with all 3 models...');
            const promises = [
                analyzeWithGemini(base64Image),
                analyzeWithOpenAI(base64Image),
                analyzeWithGroq(base64Image)
            ];
            
            const results = await Promise.allSettled(promises);
            console.log('All promises settled:', results);
            
            // End resource tracking
            resourceMetrics.endTime = performance.now();
            if (performance.memory) {
                resourceMetrics.endMemory = performance.memory.usedJSHeapSize;
            }
            
            // Display all results
            displayModelResults();
            updateAnalytics();
            
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = `
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span>Re-Analyze with AI</span>
                </div>
            `;
        });
        
        // Analyze with Gemini
        async function analyzeWithGemini(base64Image) {
            const startTime = performance.now();
            
            try {
                // Check if API key is set
                if (!GEMINI_API_KEY || GEMINI_API_KEY.trim() === '' || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
                    throw new Error('Please add your Gemini API key (FREE at ai.google.dev)');
                }
                
                const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=${GEMINI_API_KEY}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        contents: [{
                            parts: [
                                {
                                    text: getAnalysisPrompt()
                                },
                                {
                                    inline_data: {
                                        mime_type: "image/jpeg",
                                        data: base64Image
                                    }
                                }
                            ]
                        }],
                        generationConfig: {
                            temperature: 0.4,
                            topK: 32,
                            topP: 1,
                            maxOutputTokens: 1024
                        },
                        safetySettings: [
                            {
                                category: "HARM_CATEGORY_HARASSMENT",
                                threshold: "BLOCK_NONE"
                            },
                            {
                                category: "HARM_CATEGORY_HATE_SPEECH",
                                threshold: "BLOCK_NONE"
                            },
                            {
                                category: "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                                threshold: "BLOCK_NONE"
                            },
                            {
                                category: "HARM_CATEGORY_DANGEROUS_CONTENT",
                                threshold: "BLOCK_NONE"
                            }
                        ]
                    })
                });
                
                const data = await response.json();
                const endTime = performance.now();
                
                // Track network data (approximate)
                resourceMetrics.networkData += JSON.stringify(data).length;
                
                if (!response.ok) {
                    throw new Error(`API Error (${response.status}): ${data.error?.message || JSON.stringify(data)}`);
                }
                
                if (data.error) {
                    throw new Error(data.error.message || JSON.stringify(data.error));
                }
                
                if (data.candidates && data.candidates[0]?.content?.parts[0]?.text) {
                    const text = data.candidates[0].content.parts[0].text;
                    modelResults.gemini = {
                        items: parseEWasteItems(text),
                        time: Math.round(endTime - startTime),
                        text: text,
                        status: 'success',
                        error: null
                    };
                } else if (data.promptFeedback?.blockReason) {
                    throw new Error(`Content blocked: ${data.promptFeedback.blockReason}`);
                } else {
                    throw new Error('No valid response from API');
                }
            } catch (error) {
                const endTime = performance.now();
                modelResults.gemini = {
                    items: [],
                    time: Math.round(endTime - startTime),
                    text: '',
                    status: 'error',
                    error: error.message
                };
            }
        }
        
        // Analyze with OpenAI
        async function analyzeWithOpenAI(base64Image) {
            const startTime = performance.now();
            
            try {
                // Check if API key is set
                if (!OPENAI_API_KEY || OPENAI_API_KEY.trim() === '' || OPENAI_API_KEY === 'YOUR_OPENAI_API_KEY_HERE') {
                    throw new Error('Please add your OpenAI API key (Get $5 free trial at platform.openai.com)');
                }
                
                // Use OpenAI GPT-4o Mini vision model
                const response = await fetch('https://api.openai.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${OPENAI_API_KEY}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        model: 'gpt-4o-mini',
                        messages: [
                            {
                                role: 'user',
                                content: [
                                    {
                                        type: 'text',
                                        text: getAnalysisPrompt()
                                    },
                                    {
                                        type: 'image_url',
                                        image_url: {
                                            url: `data:image/jpeg;base64,${base64Image}`
                                        }
                                    }
                                ]
                            }
                        ],
                        max_tokens: 1024,
                        temperature: 0.3
                    })
                });
                
                const data = await response.json();
                const endTime = performance.now();
                
                resourceMetrics.networkData += JSON.stringify(data).length;
                
                if (!response.ok) {
                    throw new Error(`API Error (${response.status}): ${data.error?.message || JSON.stringify(data)}`);
                }
                
                if (data.error) {
                    throw new Error(data.error.message || JSON.stringify(data.error));
                }
                
                if (data.choices && data.choices[0]?.message?.content) {
                    const aiText = data.choices[0].message.content;
                    const detectedItems = parseEWasteItems(aiText);
                    
                    const text = `AI Analysis: "${aiText}"\n\nDetected e-waste items:\n${detectedItems.length > 0 ? detectedItems.map((item, i) => `${i + 1}. ${item}`).join('\n') : 'No e-waste items detected'}`;
                    
                    modelResults.openai = {
                        items: detectedItems,
                        time: Math.round(endTime - startTime),
                        text: text,
                        status: 'success',
                        error: null
                    };
                } else {
                    throw new Error('No valid response from API');
                }
            } catch (error) {
                const endTime = performance.now();
                console.error('OpenAI error:', error);
                modelResults.openai = {
                    items: [],
                    time: Math.round(endTime - startTime),
                    text: '',
                    status: 'error',
                    error: error.message.includes('API key') || error.message.includes('Please add') 
                        ? 'Get $5 free trial at platform.openai.com' 
                        : error.message
                };
            }
        }
        
        // Analyze with Groq (using Llama 4 Scout with vision)
        async function analyzeWithGroq(base64Image) {
            const startTime = performance.now();
            
            try {
                // Check if API key is set
                if (!GROQ_API_KEY || GROQ_API_KEY.trim() === '' || GROQ_API_KEY === 'YOUR_GROQ_API_KEY_HERE') {
                    throw new Error('Please add your Groq API key (FREE at console.groq.com)');
                }
                
                // Use Groq's Llama 4 Scout vision model
                const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${GROQ_API_KEY}`
                    },
                    body: JSON.stringify({
                        model: "meta-llama/llama-4-scout-17b-16e-instruct",
                        messages: [{
                            role: "user",
                            content: [
                                { type: "text", text: getAnalysisPrompt() },
                                { type: "image_url", image_url: { url: `data:image/jpeg;base64,${base64Image}` } }
                            ]
                        }],
                        temperature: 0.4,
                        max_tokens: 1024
                    })
                });
                
                const data = await response.json();
                const endTime = performance.now();
                
                resourceMetrics.networkData += JSON.stringify(data).length;
                
                if (!response.ok) {
                    throw new Error(`API Error (${response.status}): ${data.error?.message || JSON.stringify(data)}`);
                }
                
                if (data.error) {
                    throw new Error(data.error.message || JSON.stringify(data.error));
                }
                
                if (data.choices && data.choices[0]?.message?.content) {
                    const text = data.choices[0].message.content;
                    modelResults.groq = {
                        items: parseEWasteItems(text),
                        time: Math.round(endTime - startTime),
                        text: text,
                        status: 'success',
                        error: null
                    };
                } else {
                    throw new Error('No valid response');
                }
            } catch (error) {
                const endTime = performance.now();
                modelResults.groq = {
                    items: [],
                    time: Math.round(endTime - startTime),
                    text: '',
                    status: 'error',
                    error: error.message.includes('API key') || GROQ_API_KEY.includes('YOUR_') ? 'Get FREE API key at console.groq.com' : error.message
                };
            }
        }
        
        // Get analysis prompt
        function getAnalysisPrompt() {
            return "You are an expert E-Waste analyzer. Carefully examine this image and identify ALL electronic waste (e-waste) items visible. E-waste includes:\n\n**Mobile & Computing:** Mobile phones, smartphones, laptops, desktop computers, tablets, keyboards, mice, computer monitors, hard drives, USB drives, chargers, cables\n\n**Home Electronics:** TVs, refrigerators, washing machines, dryers, air conditioners, microwave ovens, toasters, vacuum cleaners, electric fans, irons\n\n**Office Equipment:** Printers, scanners, copiers, fax machines, calculators, projectors\n\n**Entertainment:** Cameras, video players, gaming consoles, speakers, headphones, earphones, music players\n\n**Power & Batteries:** Batteries (AA, AAA, lithium, phone batteries), power banks, adapters, power strips\n\n**Other:** Circuit boards, electronic components, smart home devices, fitness trackers, smartwatches, drones, remote controls\n\nProvide a NUMBERED LIST with ONE item per line. Be specific (e.g., 'Laptop Computer' not just 'computer'). If multiple similar items are visible, count them (e.g., '3x Mobile Phones'). If NO e-waste is visible, respond ONLY with: 'No e-waste items detected in this image.'\n\nFormat:\n1. [Item name]\n2. [Item name]\n3. [Item name]";
        }
        
        // Parse e-waste items from text
        function parseEWasteItems(text) {
            const items = [];
            
            if (text.toLowerCase().includes('no e-waste items detected') || 
                text.toLowerCase().includes('no electronic waste')) {
                return items;
            }
            
            const lines = text.split('\n').filter(line => line.trim());
            
            lines.forEach(line => {
                const numberedMatch = line.match(/^\d+[\.\)\:]\s*(.+)$/);
                if (numberedMatch) {
                    let cleanLine = numberedMatch[1]
                                        .replace(/^\*\*/, '')
                                        .replace(/\*\*$/, '')
                                        .replace(/^["']/, '')
                                        .replace(/["']$/, '')
                                        .trim();
                    
                    if (cleanLine && cleanLine.length > 2 && !cleanLine.toLowerCase().includes('note:') && !cleanLine.toLowerCase().includes('total:')) {
                        items.push(cleanLine);
                    }
                } else if (line.match(/^[-*•]\s/)) {
                    let cleanLine = line.replace(/^[-*•]\s*/, '')
                                        .replace(/^\*\*/, '')
                                        .replace(/\*\*$/, '')
                                        .trim();
                    if (cleanLine && cleanLine.length > 2) {
                        items.push(cleanLine);
                    }
                }
            });
            
            return items;
        }
        
        // Update model status
        function updateModelStatus(model, message, isError = false) {
            const statusDiv = document.getElementById(`${model}-status`);
            if (isError) {
                statusDiv.innerHTML = `<span class="text-red-600 text-xs">❌ ${message}</span>`;
            } else if (message === 'Analyzing...') {
                statusDiv.innerHTML = `<div class="flex items-center space-x-2"><div class="spinner" style="width:16px;height:16px;border-width:2px;"></div><span class="text-gray-600 text-xs">${message}</span></div>`;
            } else {
                statusDiv.innerHTML = `<span class="text-emerald-600 text-xs">✅ ${message}</span>`;
            }
        }
        
        // Display results for all models
        function displayModelResults() {
            // Display Gemini results
            displaySingleModelResult('gemini', modelResults.gemini, '🔷');
            
            // Display OpenAI results
            displaySingleModelResult('openai', modelResults.openai, '🤖');
            
            // Display Groq results
            displaySingleModelResult('groq', modelResults.groq, '🟢');
            
            // Update all items for facility filtering
            detectedEWasteItems = [...new Set([
                ...modelResults.gemini.items,
                ...modelResults.openai.items,
                ...modelResults.groq.items
            ])];
        }
        
        // Display single model result
        function displaySingleModelResult(modelName, result, icon) {
            const resultsDiv = document.getElementById(`${modelName}-results`);
            
            if (result.status === 'error') {
                updateModelStatus(modelName, result.error, true);
                resultsDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-red-800 mb-1">Analysis Failed</h4>
                                <p class="text-sm text-red-700">${result.error}</p>
                                ${result.error.includes('API key') ? `
                                    <div class="mt-2 text-xs text-red-600">
                                        <p>To enable this model, please:</p>
                                        <ol class="list-decimal ml-4 mt-1 space-y-1">
                                            <li>Get API key from ${modelName === 'openai' ? 'https://platform.openai.com/api-keys ($5 trial)' : 'https://console.groq.com/ (FREE)'}</li>
                                            <li>Add it to the code: ${modelName.toUpperCase()}_API_KEY</li>
                                        </ol>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
                return;
            }
            
            updateModelStatus(modelName, `Completed in ${result.time}ms`);
            
            if (result.items.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-amber-800 mb-1">No E-Waste Detected</h4>
                                <p class="text-sm text-amber-700">This model couldn't identify any electronic waste items.</p>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Success - show items
            let html = `
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold text-emerald-800">Detected ${result.items.length} item${result.items.length > 1 ? 's' : ''}!</span>
                        </div>
                        <span class="metric-badge text-xs">${result.time}ms</span>
                    </div>
                </div>
                <div class="space-y-2">
            `;
            
            result.items.forEach((item, index) => {
                let itemIcon = getItemIcon(item);
                html += `
                    <div class="flex items-center space-x-3 bg-white rounded-lg px-4 py-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <span class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">${index + 1}</span>
                        <span class="text-2xl">${itemIcon}</span>
                        <span class="text-gray-800 font-medium flex-1">${item}</span>
                    </div>
                `;
            });
            
            html += `</div>`;
            resultsDiv.innerHTML = html;
        }
        
        // Get item icon
        function getItemIcon(item) {
            const itemLower = item.toLowerCase();
            if (itemLower.includes('phone') || itemLower.includes('mobile')) return '📱';
            if (itemLower.includes('laptop') || itemLower.includes('computer')) return '💻';
            if (itemLower.includes('monitor') || itemLower.includes('screen')) return '🖥️';
            if (itemLower.includes('tv') || itemLower.includes('television')) return '📺';
            if (itemLower.includes('battery') || itemLower.includes('charger')) return '🔋';
            if (itemLower.includes('printer')) return '🖨️';
            if (itemLower.includes('keyboard')) return '⌨️';
            if (itemLower.includes('mouse')) return '🖱️';
            if (itemLower.includes('camera')) return '📷';
            if (itemLower.includes('speaker') || itemLower.includes('headphone')) return '🔊';
            if (itemLower.includes('tablet')) return '📱';
            return '♻️';
        }
        
        // Update analytics dashboard
        function updateAnalytics() {
            // Find fastest model
            const successfulModels = Object.entries(modelResults).filter(([_, r]) => r.status === 'success');
            
            if (successfulModels.length === 0) {
                document.getElementById('fastest-model').textContent = 'N/A';
                document.getElementById('fastest-time').textContent = 'No successful analyses';
                return;
            }
            
            const fastest = successfulModels.reduce((prev, curr) => 
                curr[1].time < prev[1].time ? curr : prev
            );
            document.getElementById('fastest-model').textContent = fastest[0].charAt(0).toUpperCase() + fastest[0].slice(1);
            document.getElementById('fastest-time').textContent = `${fastest[1].time}ms`;
            
            // Find model with most items
            const mostItems = successfulModels.reduce((prev, curr) => 
                curr[1].items.length > prev[1].items.length ? curr : prev
            );
            document.getElementById('most-items-model').textContent = mostItems[0].charAt(0).toUpperCase() + mostItems[0].slice(1);
            document.getElementById('most-items-count').textContent = `${mostItems[1].items.length} items`;
            
            // Calculate efficiency (items per second)
            const efficiencies = successfulModels.map(([name, result]) => ({
                name,
                score: result.items.length > 0 ? (result.items.length / (result.time / 1000)).toFixed(2) : 0
            }));
            const bestEff = efficiencies.reduce((prev, curr) => 
                parseFloat(curr.score) > parseFloat(prev.score) ? curr : prev
            );
            document.getElementById('best-efficiency').textContent = bestEff.name.charAt(0).toUpperCase() + bestEff.name.slice(1);
            document.getElementById('efficiency-score').textContent = `${bestEff.score} items/sec`;
            
            // Update charts
            updateTimingChart();
            updateMetricsTable();
            updateResourceChart();
            updateCostChart();
        }
        
        // Update timing chart
        function updateTimingChart() {
            const ctx = document.getElementById('timingChart').getContext('2d');
            
            if (window.timingChart && typeof window.timingChart.destroy === 'function') {
                window.timingChart.destroy();
            }
            
            const modelNames = Object.keys(modelResults);
            const times = modelNames.map(name => modelResults[name]?.time || 0);
            const colors = ['#3b82f6', '#f97316', '#22c55e'];
            
            window.timingChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Gemini 2.0 Flash', 'OpenAI GPT-4o Mini', 'Llama 4 Scout (Groq)'],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: times,
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y}ms`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'ms';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Update metrics table
        function updateMetricsTable() {
            const tbody = document.getElementById('metricsTable');
            const modelNames = ['gemini', 'openai', 'groq'];
            const displayNames = ['Gemini 2.0 Flash', 'OpenAI GPT-4o Mini', 'Llama 4 Scout 17B'];
            
            let html = '';
            modelNames.forEach((name, idx) => {
                const result = modelResults[name] || { status: 'pending', time: 0, items: [], text: '' };
                const score = result.status === 'success' && result.items.length > 0 
                    ? ((result.items.length / (result.time / 1000)) * 10).toFixed(1) 
                    : '0';
                
                html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-800">${displayNames[idx]}</td>
                        <td class="text-center py-3 px-2 ${result.status === 'error' ? 'text-red-600' : 'text-gray-700'}">${result.status === 'error' ? 'Error' : result.time + 'ms'}</td>
                        <td class="text-center py-3 px-2 text-gray-700">${result.items?.length || 0}</td>
                        <td class="text-center py-3 px-2 text-gray-700">${result.text?.length || 0}</td>
                        <td class="text-center py-3 px-2">
                            <span class="metric-badge text-xs">${score}</span>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }
        
        // Update resource chart
        function updateResourceChart() {
            const totalTime = resourceMetrics.endTime - resourceMetrics.startTime;
            const memoryUsed = performance.memory 
                ? ((resourceMetrics.endMemory - resourceMetrics.startMemory) / 1024 / 1024).toFixed(2)
                : 0;
            const networkKB = (resourceMetrics.networkData / 1024).toFixed(2);
            
            document.getElementById('memory-usage').textContent = performance.memory ? `${memoryUsed} MB` : 'N/A';
            document.getElementById('network-usage').textContent = `${networkKB} KB`;
            document.getElementById('total-time').textContent = `${Math.round(totalTime)}ms`;
            
            const ctx = document.getElementById('resourceChart').getContext('2d');
            
            if (window.resourceChart && typeof window.resourceChart.destroy === 'function') {
                window.resourceChart.destroy();
            }
            
            window.resourceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Gemini', 'OpenAI', 'Groq'],
                    datasets: [{
                        label: 'Time Distribution',
                        data: [modelResults.gemini?.time || 0, modelResults.openai?.time || 0, modelResults.groq?.time || 0],
                        backgroundColor: ['#3b82f6', '#f97316', '#22c55e'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Update cost chart
        function updateCostChart() {
            const ctx = document.getElementById('costChart').getContext('2d');
            
            if (window.costChart && typeof window.costChart.destroy === 'function') {
                window.costChart.destroy();
            }
            
            // Estimated costs per 1000 images
            const costs = [0, 0, 0]; // All FREE! Gemini, OpenAI ($5 trial), Groq
            
            window.costChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Gemini 2.0 Flash', 'OpenAI GPT-4o Mini', 'Llama 4 Scout (Groq)'],
                    datasets: [{
                        label: 'Cost per 1000 images ($)',
                        data: costs,
                        backgroundColor: ['#3b82f6', '#f97316', '#22c55e'],
                        borderColor: ['#3b82f6', '#f97316', '#22c55e'],
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y === 0 ? 'FREE' : '$' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value === 0 ? 'FREE' : '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                button.classList.add('active');
                const tabName = button.getAttribute('data-tab');
                document.getElementById(`${tabName}-tab`).classList.add('active');
            });
        });
        
        // Parse and display e-waste items from AI response (Legacy function - kept for compatibility)
        function parseAndDisplayEWasteItems(aiResponse) {
            const detectedItemsDiv = document.getElementById('detectedItems');
            detectedItemsDiv.innerHTML = '';
            detectedEWasteItems = [];
            
            console.log('AI Response:', aiResponse);
            
            // Check if no items found
            if (aiResponse.toLowerCase().includes('no e-waste items detected') || 
                aiResponse.toLowerCase().includes('no electronic waste') ||
                aiResponse.toLowerCase().includes('no e-waste visible')) {
                detectedItemsDiv.innerHTML = `
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-amber-800 mb-1">No E-Waste Detected</h4>
                                <p class="text-sm text-amber-700">The AI couldn't identify any electronic waste items in this image.</p>
                                <p class="text-xs text-amber-600 mt-2">💡 Try taking a clearer photo with better lighting, or upload a different image.</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('aiResults').classList.remove('hidden');
                return;
            }
            
            // Parse the response to extract items
            const lines = aiResponse.split('\n').filter(line => line.trim());
            
            // Extract items from numbered list
            lines.forEach(line => {
                // Match numbered items: "1. Item", "1) Item", or "1: Item"
                const numberedMatch = line.match(/^\d+[\.\)\:]\s*(.+)$/);
                if (numberedMatch) {
                    let cleanLine = numberedMatch[1]
                                        .replace(/^\*\*/, '')
                                        .replace(/\*\*$/, '')
                                        .replace(/^["']/, '')
                                        .replace(/["']$/, '')
                                        .trim();
                    
                    if (cleanLine && cleanLine.length > 2 && !cleanLine.toLowerCase().includes('note:') && !cleanLine.toLowerCase().includes('total:')) {
                        detectedEWasteItems.push(cleanLine);
                    }
                }
                // Also match bullet points
                else if (line.match(/^[-*•]\s/)) {
                    let cleanLine = line.replace(/^[-*•]\s*/, '')
                                        .replace(/^\*\*/, '')
                                        .replace(/\*\*$/, '')
                                        .trim();
                    if (cleanLine && cleanLine.length > 2) {
                        detectedEWasteItems.push(cleanLine);
                    }
                }
            });
            
            // Display detected items
            if (detectedEWasteItems.length > 0) {
                // Show success header
                const headerDiv = document.createElement('div');
                headerDiv.className = 'bg-emerald-50 border border-emerald-200 rounded-lg p-3 mb-3';
                headerDiv.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-emerald-800">Successfully detected ${detectedEWasteItems.length} e-waste item${detectedEWasteItems.length > 1 ? 's' : ''}!</span>
                    </div>
                `;
                detectedItemsDiv.appendChild(headerDiv);
                
                // Show items
                const itemsContainer = document.createElement('div');
                itemsContainer.className = 'space-y-2 mb-3';
                
                detectedEWasteItems.forEach((item, index) => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'flex items-center space-x-3 bg-white rounded-lg px-4 py-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow';
                    
                    // Determine item icon based on category
                    let itemIcon = '♻️';
                    const itemLower = item.toLowerCase();
                    if (itemLower.includes('phone') || itemLower.includes('mobile')) itemIcon = '📱';
                    else if (itemLower.includes('laptop') || itemLower.includes('computer')) itemIcon = '💻';
                    else if (itemLower.includes('monitor') || itemLower.includes('screen')) itemIcon = '🖥️';
                    else if (itemLower.includes('tv') || itemLower.includes('television')) itemIcon = '📺';
                    else if (itemLower.includes('battery') || itemLower.includes('charger')) itemIcon = '🔋';
                    else if (itemLower.includes('printer')) itemIcon = '🖨️';
                    else if (itemLower.includes('keyboard')) itemIcon = '⌨️';
                    else if (itemLower.includes('mouse')) itemIcon = '🖱️';
                    else if (itemLower.includes('camera')) itemIcon = '📷';
                    else if (itemLower.includes('speaker') || itemLower.includes('headphone')) itemIcon = '🔊';
                    else if (itemLower.includes('tablet')) itemIcon = '📱';
                    
                    itemDiv.innerHTML = `
                        <span class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">${index + 1}</span>
                        <span class="text-2xl">${itemIcon}</span>
                        <span class="text-gray-800 font-medium flex-1">${item}</span>
                    `;
                    itemsContainer.appendChild(itemDiv);
                });
                
                detectedItemsDiv.appendChild(itemsContainer);
                
                // Show recommendation message with action
                const recommendationDiv = document.createElement('div');
                recommendationDiv.className = 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4';
                recommendationDiv.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="text-2xl">💡</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-blue-900 mb-2">Ready to Recycle!</h4>
                            <p class="text-sm text-blue-800 mb-3">
                                We've identified your e-waste items. Now let's find the best recycling centers near you that can handle these items!
                            </p>
                            <button onclick="document.getElementById('locationBtn').click(); document.getElementById('locationBtn').scrollIntoView({behavior: 'smooth', block: 'center'});" 
                                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition-all text-sm flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span>Find Recycling Centers Now</span>
                            </button>
                        </div>
                    </div>
                `;
                detectedItemsDiv.appendChild(recommendationDiv);
            } else {
                // Fallback if parsing failed
                detectedItemsDiv.innerHTML = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-semibold text-yellow-800 mb-1">Unexpected Response Format</h4>
                                <p class="text-sm text-yellow-700 mb-2">The AI responded but in an unexpected format. Here's what it said:</p>
                                <div class="bg-white rounded p-3 text-xs text-gray-700 max-h-32 overflow-y-auto">
                                    ${aiResponse.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                                </div>
                                <p class="text-xs text-yellow-600 mt-2">Please try taking another photo or upload a clearer image.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('aiResults').classList.remove('hidden');
        }
        
        // Event listeners
        document.getElementById('locationBtn').addEventListener('click', getUserLocation);
        
        // Filter panel functionality
        document.getElementById('filterToggle').addEventListener('click', () => {
            document.getElementById('filterPanel').style.transform = 'translateX(0)';
        });
        
        document.getElementById('closeFilter').addEventListener('click', () => {
            document.getElementById('filterPanel').style.transform = 'translateX(100%)';
        });
        
        // Filter controls
        document.getElementById('distanceRange').addEventListener('input', (e) => {
            updateDistanceValue();
            filters.maxDistance = parseInt(e.target.value);
        });
        
        document.getElementById('ratingRange').addEventListener('input', (e) => {
            updateRatingValue();
            filters.minRating = parseFloat(e.target.value);
        });
        
        document.querySelectorAll('input[name="openStatus"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    filters.openStatus = e.target.value;
                }
            });
        });
        
        document.querySelectorAll('.price-level').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                filters.priceLevels = Array.from(document.querySelectorAll('.price-level:checked'))
                    .map(cb => cb.value);
            });
        });
        
        document.getElementById('sortBy').addEventListener('change', (e) => {
            filters.sortBy = e.target.value;
        });
        
        document.getElementById('applyFilters').addEventListener('click', () => {
            applyFiltersAndDisplay();
            document.getElementById('filterPanel').style.transform = 'translateX(100%)';
        });
        
        document.getElementById('clearFilters').addEventListener('click', () => {
            // Reset all filters
            filters.maxDistance = 5;
            filters.minRating = 0;
            filters.openStatus = 'all';
            filters.priceLevels = [];
            filters.sortBy = 'distance';
            
            // Reset UI
            document.getElementById('distanceRange').value = 5;
            document.getElementById('ratingRange').value = 0;
            document.querySelector('input[name="openStatus"][value="all"]').checked = true;
            document.querySelectorAll('.price-level').forEach(cb => cb.checked = false);
            document.getElementById('sortBy').value = 'distance';
            
            updateDistanceValue();
            updateRatingValue();
            applyFiltersAndDisplay();
        });
        
        // Modal functionality
        function closeModal() {
            document.getElementById('facilityModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });
        
        document.getElementById('facilityModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeModal();
            }
        });
        
        // Initialize filter values
        updateDistanceValue();
        updateRatingValue();
        
        // Initialize map when page loads
        window.addEventListener('load', initMap);
    </script>
</body>
</html>