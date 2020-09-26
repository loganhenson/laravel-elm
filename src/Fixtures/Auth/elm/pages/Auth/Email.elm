module Auth.Email exposing (..)

import Auth.Layout exposing (authButtonClasses, authContainer, authErrors, authInput, authLink, authStatus)
import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (attribute, class, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, nullable, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, Page, page)
import Routes exposing (get, post)


type alias Props =
    { errors : Errors
    , status : Maybe String
    }


type alias State =
    { email : String }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetEmail String
    | BackToLogin


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
        |> required "status" (nullable string)


stateFromProps : Props -> State
stateFromProps props =
    { email = "" }


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        BackToLogin ->
            ( { props = props, state = state }
            , get Routes.login
            )

        SetEmail email ->
            ( { props = props, state = { state | email = email } }
            , Cmd.none
            )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.passwordEmail )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "email", Json.Encode.string state.email )
                            ]
                      )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div []
        [ authContainer "Reset Password"
            [ authStatus props.status
            , form [ onSubmit <| Submit ]
                [ div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetEmail state.email props.errors "Email" "email" [ attribute "autofocus" "", attribute "required" "", type_ "email", attribute "autocomplete" "email" ]
                    , authErrors props.errors "email"
                    ]
                , div [ class "flex flex-wrap" ]
                    [ button [ class authButtonClasses, type_ "submit" ]
                        [ text "Send Password Reset Link" ]
                    ]
                ]
            ]
        , div [ class "w-full text-center" ]
            [ text "Don't have an account?"
            , authLink BackToLogin "ml-6" [ text "Sign up" ]
            ]
        ]
