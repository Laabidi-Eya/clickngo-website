let mixer, avatar, clock = new THREE.Clock();
const dialogBox = document.getElementById("assistant-dialog");

async function init() {
  // THREE.js Scene
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 1000);
  camera.position.set(0, 1.6, 3);

  const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById("avatar-canvas"), alpha: true });
  renderer.setSize(400, 400);
  renderer.setPixelRatio(window.devicePixelRatio);

  const light = new THREE.HemisphereLight(0xffffff, 0x444444, 1);
  scene.add(light);

  const loader = new THREE.GLTFLoader();
  loader.load('models/avatar.glb', gltf => {
    avatar = gltf.scene;
    scene.add(avatar);
    if (gltf.animations.length) {
      mixer = new THREE.AnimationMixer(avatar);
      mixer.clipAction(gltf.animations[0]).play();
    }
  });

  animate();

  // Scroll interaction
  window.addEventListener('scroll', () => {
    if (avatar) avatar.rotation.y += 0.05;
    say("Tu scrolles, hein ? 👀");
  });

  // FaceAPI
  await loadFaceAPI();
  startVideo();
  detectEmotionLoop();
}

function animate() {
  requestAnimationFrame(animate);
  const delta = clock.getDelta();
  if (mixer) mixer.update(delta);
  if (avatar) avatar.rotation.y += 0.001;
  renderer.render(scene, camera);
}

function say(text) {
  dialogBox.textContent = text;
}

// FaceAPI Setup
async function loadFaceAPI() {
  await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
  await faceapi.nets.faceExpressionNet.loadFromUri('/models');
}

function startVideo() {
  navigator.mediaDevices.getUserMedia({ video: {} })
    .then(stream => document.getElementById('video').srcObject = stream)
    .catch(err => console.error("Webcam error:", err));
}

async function detectEmotionLoop() {
  const video = document.getElementById('video');
  const result = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
  if (result) {
    const expressions = result.expressions;
    const emotion = Object.keys(expressions).reduce((a, b) => expressions[a] > expressions[b] ? a : b);
    handleEmotion(emotion);
  }
  setTimeout(detectEmotionLoop, 2000);
}

function handleEmotion(emotion) {
  switch (emotion) {
    case 'happy':
      say("Tu as l’air content 😄 ! Une petite soirée à Sousse ?");
      break;
    case 'sad':
    case 'angry':
      say("T’as pas l’air bien… Spa à Hammamet ?");
      break;
    case 'neutral':
      say("T’as une vibe neutre… On chill ?");
      break;
    default:
      say("Je capte pas bien ton humeur 🤖");
  }
}
