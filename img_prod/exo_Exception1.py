"""Définit les classes propres à notre forum. """
class Thread:
    """Fil de discussions."""
    def __init__(self, title, time_posted, post):
        self.title = title
        self.time_posted = time_posted
        self.posts = [post]
    def display(self):
        """Affiche le fil de discussion."""
        print("----- THREAD -----")
        print("titre:", self.title, "date:", self.time_posted)
        print()
        for post in self.posts:
            post.display()
            print()
        print("------------------")
    def add_post(self, post):
        """Ajoute un post."""
        self.posts.append(post)

class User:
    """Utilisateur."""
    def __init__(self, username, password):
        """Initialise le nom d'utilisateur et le mot de passe."""
        self.username = username
        self.password = password
        if len(username) < 3:
            raise ErreurLoginException(username)
        if len(password) < 10:
            raise ErreurLoginException(username)
                
        
         
    def login(self):
        """Connecte l'utilisateur."""
        print("L'utilisateur", self.username ,"est connecté.")
    def post(self, thread, content):
        """Poste un message dans un fil de discussion."""
        post = Post(user=self, time_posted="aujourd'hui", content=content)
        thread.add_post(post)
        return post
    def make_thread(self, title, content):
        """Créé un nouveau fil de discussion."""
        post = Post(self, "aujourd'hui", content)
        return Thread(title, "aujourd'hui", post)
    def __str__(self):
        """représentation de l'utilisateur."""
        return self.username
    
    
class ErreurUser(Exception):
    def __init__(self,username,password):
        self.username = "Votre nom d'utilisateur est trop court."
        
    def display(self):
        print("Changez votre nom d'utilisateur actuel :", self.username)
        
class ErreurPassword(Exception):
    def __init__(self, mdp):
        self.mdp = "Votre mot de passe n'est pas assez complexe"
        
    def display(self):
        print("Changez votre mot de passe")



class Moderator(User):
    """Utilisateur modérateur."""
    def edit(self, post, content):
        """Modifie un message."""
        post.content = content
    def delete(self, thread, post):
        """Supprime un message."""
        index = thread.posts.index(post)
        del thread.posts[index]
    def login(self):
        """Connecte l'utilisateur."""
        print("Le Modérateur", self.username ,"est connecté.")

class Post:
    """Message."""
    def __init__(self, user, time_posted, content):
        """Initialise l'utilisateur, la date et le contenu."""
        self.user = user
        self.time_posted = time_posted
        self.content = content
    def display(self):
        """Affiche le message."""
        print("Message posté par", self.user ,"le", self.time_posted,":")
        print(self.content)






"""Lance le code principal."""
user = User("John", "superpassword")
moderator = Moderator("Lucie", "helloworld")

cake_thread = user.make_thread("Gâteau à la vanille ???", "Vous aimez ou non ?")
cake_thread.display()

irrelevant_post = user.post(cake_thread, content="Et vous aimez les voitures ?")
response = moderator.post(cake_thread, content="C'est hors sujet sur ce forum ")
cake_thread.display()

print()
print("après quelques minutes, le modérateur supprime les messages hors sujets...")
print()

moderator.delete(cake_thread, irrelevant_post)
moderator.delete(cake_thread, response)

cake_thread.display()
