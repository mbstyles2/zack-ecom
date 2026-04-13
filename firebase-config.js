// firebase-config.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

const firebaseConfig = {
  apiKey: "AIzaSyBf0RpUSdLV3NWIDO4vMorUBvRikBuoY-Q",
  authDomain: "zack-2025.firebaseapp.com",
  projectId: "zack-2025",
  storageBucket: "zack-2025.appspot.com",
  messagingSenderId: "750028128870",
  appId: "1:750028128870:web:aef937d12dd9ad372f23a7",
  measurementId: "G-D5R80DWW73"
};

const app = initializeApp(firebaseConfig);

export const auth = getAuth(app);
export const db = getFirestore(app);