import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.openqa.selenium.By;
import java.util.Random;
import java.util.concurrent.TimeUnit;

public class RegisterTest {
	// Create random string
	public static String makeString(int length) {
		Random rand = new Random();
		String s = "";
		for (int i = 0; i < length; i++)
			s += (char)(rand.nextInt(26) + 'a');
		return s;
	}
	
    public static void main(String[] args) {
    	try {
	    	System.setProperty("webdriver.chrome.driver", "chromedriver.exe");
	    	WebDriver driver = new ChromeDriver();
	    	// Wait for one second if element is still loading
	    	driver.manage().timeouts().implicitlyWait(1, TimeUnit.SECONDS);
	    	
	    	// Start at login
	        driver.get(args[0]);
	        
	        // Go to registration
	        driver.findElement(By.xpath("//button[text()='Zarejestruj siê']")).click();
	        
	        // Type in data and register
	        String user = makeString(6), pass = makeString(8);
	        driver.findElement(By.name("login")).sendKeys(user);
	        driver.findElement(By.name("password")).sendKeys(pass);
	        driver.findElement(By.name("password-check")).sendKeys(pass);
	        driver.findElement(By.id("form-submit")).click();
	        
	        // Log out
	        Thread.sleep(1000);
	        driver.findElement(By.xpath("//div[@class='dropdown-menu-button']")).click();
	        driver.findElement(By.linkText("Wyloguj siê")).click();
	        
	        // Try to log in again
	        driver.findElement(By.name("login")).sendKeys(user);
	        driver.findElement(By.name("password")).sendKeys(pass);
	        driver.findElement(By.id("form-submit")).click();
	        
	        // Log out again
	        Thread.sleep(1000);
	        driver.findElement(By.xpath("//div[@class='dropdown-menu-button']")).click();
	        driver.findElement(By.linkText("Wyloguj siê")).click();
	        
	        //driver.quit();
    	} catch (InterruptedException e) {
    		
    	}
    }
}