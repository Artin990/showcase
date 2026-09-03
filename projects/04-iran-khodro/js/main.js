"use strict";

window.addEventListener("load", () => {
  const preloader = document.querySelector(".preloader");
  setTimeout(() => {
    preloader.classList.add("hidden");
  }, 2000);
});

const menuBtn = document.getElementById('menuBtn');
const closeBtn = document.getElementById('closeBtn');
const menuOverlay = document.getElementById('menuOverlay');

menuBtn.addEventListener('click', () => {
    menuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
});

closeBtn.addEventListener('click', () => {
    menuOverlay.classList.remove('active');
    document.body.style.overflow = '';
});


document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && menuOverlay.classList.contains('active')) {
        menuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
});


const navLinks = document.querySelectorAll('.nav-link');
const menuSections = document.querySelectorAll('.menu-section');

navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const sectionName = link.getAttribute('data-section');
        
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        
        menuSections.forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${sectionName}-section`).classList.add('active');
    });
});


let cart = [];
const MAX_ITEMS = 2;

const modelItems = document.querySelectorAll('.model-item');
modelItems.forEach(item => {
    item.addEventListener('click', () => {
        const carName = item.querySelector('h3').textContent;
        const carImage = item.querySelector('img').src;
        const carPrice = parseInt(item.getAttribute('data-price'));
        const carId = item.getAttribute('data-car');
        
        addToCart({
            id: carId,
            name: carName,
            image: carImage,
            price: carPrice
        });
        
        document.querySelector('[data-section="buy"]').click();
    });
});

function addToCart(car) {
    const existingItem = cart.find(item => item.id === car.id);
    
    if (existingItem) {
        if (existingItem.quantity < MAX_ITEMS) {
            existingItem.quantity++;
        } else {
            alert(`حداکثر ${MAX_ITEMS} عدد از هر خودرو می‌توانید سفارش دهید`);
            return;
        }
    } else {
        cart.push({
            ...car,
            quantity: 1
        });
    }
    
    updateCartDisplay();
}

function removeFromCart(carId) {
    cart = cart.filter(item => item.id !== carId);
    updateCartDisplay();
}

function updateQuantity(carId, delta) {
    const item = cart.find(item => item.id === carId);
    if (!item) return;
    
    const newQuantity = item.quantity + delta;
    
    if (newQuantity < 1) {
        removeFromCart(carId);
    } else if (newQuantity <= MAX_ITEMS) {
        item.quantity = newQuantity;
        updateCartDisplay();
    } else {
        alert(`حداکثر ${MAX_ITEMS} عدد از هر خودرو می‌توانید سفارش دهید`);
    }
}

function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cartItems');
    const cartSummary = document.getElementById('cartSummary');
    const totalCountEl = document.getElementById('totalCount');
    const totalPriceEl = document.getElementById('totalPrice');
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart">هیچ خودرویی انتخاب نشده است</p>';
        cartSummary.style.display = 'none';
        return;
    }
    
    cartItemsContainer.innerHTML = cart.map(item => `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}" class="cart-item-image" />
            <div class="cart-item-info">
                <h3>${item.name}</h3>
                <p>${formatPrice(item.price)} تومان</p>
            </div>
            <div class="cart-item-quantity">
                <button class="qty-btn" onclick="updateQuantity('${item.id}', -1)">
                    <i class='bx bx-minus'></i>
                </button>
                <span class="qty-value">${item.quantity}</span>
                <button class="qty-btn" onclick="updateQuantity('${item.id}', 1)" ${item.quantity >= MAX_ITEMS ? 'disabled' : ''}>
                    <i class='bx bx-plus'></i>
                </button>
            </div>
            <button class="remove-btn" onclick="removeFromCart('${item.id}')">
                <i class='bx bx-x'></i>
            </button>
        </div>
    `).join('');
    
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    totalCountEl.textContent = totalCount;
    totalPriceEl.textContent = formatPrice(totalPrice) + ' تومان';
    
    cartSummary.style.display = 'block';
}

function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


document.getElementById('continueBtn')?.addEventListener('click', () => {
    if (cart.length === 0) return;
    
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const items = cart.map(item => `${item.name} (${item.quantity} عدد)`).join('\n');
    
    alert(`سفارش شما:\n\n${items}\n\nمجموع: ${formatPrice(totalPrice)} تومان\n\nخرید شما با موفقیت ثبت شد!`);

    cart = [];
    updateCartDisplay();
});

window.updateQuantity = updateQuantity;
window.removeFromCart = removeFromCart;


const serviceCards = document.querySelectorAll('.service-card');
serviceCards.forEach(card => {
    card.addEventListener('click', () => {
        const link = card.getAttribute('data-link');
        alert(`انتقال به صفحه ${card.querySelector('h3').textContent}...\n(این صفحه هنوز ساخته نشده است)`);
    });
});


const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const animateOnScroll = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

document.addEventListener('DOMContentLoaded', () => {
    
// 1. Header Animation
    animateHeader();

// 2. After Hero Cards Animation
    animateAfterHeroCards();
    
// 3. Car Type Section Animation
    animateCarTypeSection();
    
// 4. Before Footer Cards Animation  
    animateBeforeFooterCards();
    
// 5. Footer Animation
    animateFooter();
    
// 6. Additional hover effects
    addHoverEffects();
});

// 1. Header Animation
function animateHeader() {
    const header = document.querySelector('.site-header');
    if (header) {
        setTimeout(() => {
            header.style.animation = 'fadeInDown 0.8s ease-out forwards';
        }, 100);
    }
}

// 2. After Hero Cards Animation
function animateAfterHeroCards() {
    const cards = document.querySelectorAll('.after-hero-card > div');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px)';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        card.style.animation = `slideUpFade 0.6s ease-out forwards`;
                    }, index * 150);
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(card);
    });
}

// 3. Car Type Section Animation
function animateCarTypeSection() {
    const cards = document.querySelectorAll('.car-type-sec > div');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9) translateY(30px)';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1) translateY(0)';
                    }, index * 100);
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(card);
    });
}

// 4. Before Footer Cards Animation
function animateBeforeFooterCards() {
    const cards = document.querySelectorAll('.before-footer-card > div');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateX(50px)';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        card.style.transition = 'all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1)';
                        card.style.opacity = '1';
                        card.style.transform = 'translateX(0)';
                    }, index * 200);
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(card);
    });
}

// 5. Footer Animation
function animateFooter() {
    const footer = document.querySelector('.footer-sec');
    if (footer) {
        footer.style.opacity = '0';
        footer.style.transform = 'translateY(50px)';
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    footer.style.transition = 'all 1s ease-out';
                    footer.style.opacity = '1';
                    footer.style.transform = 'translateY(0)';
                    observer.unobserve(footer);
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(footer);
    }
}

// 6. Additional Hover Effects
function addHoverEffects() {
    // Arrow animations on hover
    const arrows = document.querySelectorAll('.arrow, .bx-arrow-back');
    arrows.forEach(arrow => {
        const parent = arrow.closest('[class*="card-"], .model-item');
        if (parent) {
            parent.addEventListener('mouseenter', () => {
                arrow.style.animation = 'arrowBounce 0.6s ease-in-out infinite';
            });
            
            parent.addEventListener('mouseleave', () => {
                arrow.style.animation = '';
            });
        }
    });
}
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

window.addEventListener("scroll", () => {
  const scrollTop = window.scrollY;
  const docHeight = document.body.scrollHeight - window.innerHeight;
  const scrollPercent = (scrollTop / docHeight) * 100;
  const progressBar = document.querySelector(".scroll-progress");
  progressBar.style.width = scrollPercent + "%";
});

const goTopBtn = document.querySelector(".goTop");

window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    goTopBtn.style.display = "block";
  } else {
    goTopBtn.style.display = "none";
  }
};

function scrollToTop() {
  const scrollDuration = 800;
  const scrollStep = -window.scrollY / (scrollDuration / 15);

  function scrollAnimation() {
    if (window.scrollY !== 0) {
      window.scrollBy(0, scrollStep);
      requestAnimationFrame(scrollAnimation);
    }
  }

  requestAnimationFrame(scrollAnimation);
}
goTopBtn.addEventListener("click", scrollToTop);