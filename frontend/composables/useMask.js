export const useMask = () => {
  
  // İsim Maskeleme Fonksiyonu (KVKK)
  const maskName = (fullName) => {
    if (!fullName) return '***';
    
    // İsimleri boşluktan böl
    const parts = fullName.trim().split(' ');
    
    return parts.map(part => {
      // Sadece ilk harfi al, gerisine 3 yıldız koy
      return part.charAt(0).toUpperCase() + '***';
    }).join(' ');
  };

  return {
    maskName
  };
};